<?php

declare(strict_types=1);

namespace YiiSoft\Billing\ActiveRecord\Repository;

use DateTimeImmutable;
use InvalidArgumentException;
use Money\Money;
use RuntimeException;
use Throwable;
use Yiisoft\ActiveRecord\ActiveRecordFactory;
use YiiSoft\Billing\ActiveRecord\Record\OperationRecord;
use YiiSoft\Billing\ActiveRecord\Record\TransactionEntryRecord;
use YiiSoft\Billing\Entity\Account;
use YiiSoft\Billing\Entity\Read\Operation as OperationRead;
use YiiSoft\Billing\Entity\Transaction;
use YiiSoft\Billing\Entity\Write\Operation;
use YiiSoft\Billing\Repository\AccountRepository;
use YiiSoft\Billing\Repository\OperationRepositoryInterface;
use Yiisoft\Db\Connection\Connection;

class OperationRepository implements OperationRepositoryInterface
{
    private AccountRepository $accountRepository;
    private ActiveRecordFactory $factory;
    private Connection $connection;

    public function __construct(
        AccountRepository $accountRepository,
        ActiveRecordFactory $factory,
        Connection $connection
    ) {
        $this->accountRepository = $accountRepository;
        $this->factory = $factory;
        $this->connection = $connection;
    }

    public function perform(Operation $operation): OperationRead
    {
        if (count($operation->getTransactions()) === 0) {
            throw new InvalidArgumentException('Transaction list is empty');
        }

        $dbTransaction = $this->connection->beginTransaction();
        $operationTime = new DateTimeImmutable();

        try {
            $this->checkOutOfFunds($operation);

            /** @var OperationRecord $operationRecord */
            $operationRecord = $this->factory->createAR(OperationRecord::class);
            $operationRecord->createdAt = $operationTime->getTimestamp();
            if (!$operationRecord->save()) {
                throw new RuntimeException('Cannot persist transaction');
            }
            $operationId = (string) $operationRecord->id;

            $transactions = [];

            foreach ($operation->getTransactions() as $transaction) {
                $recordPayer = $this->createTransactionEntry(
                    $operationTime,
                    $transaction->getFunds(),
                    $transaction->getPayer(),
                    $operationId
                );
                $recordRecipient = $this->createTransactionEntry(
                    $operationTime,
                    $transaction->getFunds(),
                    $transaction->getRecipient(),
                    $operationId
                );

                $transactions[] = new Transaction(
                    $transaction->getFunds(),
                    $transaction->getPayer(),
                    $transaction->getRecipient(),
                    "$recordPayer->id-$recordRecipient->id",
                    $operationTime,
                );
            }

            $dbTransaction->commit();

            return new OperationRead($operationId, ...$transactions);
        } catch (Throwable $exception) {
            $dbTransaction->rollBack();

            throw $exception;
        }
    }

    // TODO Move this method to the core library as a standalone service
    private function checkOutOfFunds(Operation $operation): void
    {
        /** @var Money[][] $amounts */
        $payers = $amounts = [];
        foreach ($operation->getTransactions() as $transaction) {
            $amount = $transaction->getFunds();
            $currencyCode = $amount->getCurrency()->getCode();
            $payer = $transaction->getPayer();
            if (!isset($payers[$payer->getId()])) {
                $payers[$payer->getId()] = $payer;
                $amounts[$payer->getId()] = [];
            }

            $amountOld = $amounts[$payer->getId()][$currencyCode] ?? new Money(0, $amount->getCurrency());
            $amounts[$payer->getId()][$currencyCode] = $amountOld->add($amount);
        }

        foreach ($amounts as $payerId => $currencies) {
            $payer = $payers[$payerId];
            foreach ($currencies as $amount) {
                $payerFunds = $this->accountRepository->getFunds($payer, $amount->getCurrency());

                if (
                    ($payer->getType()->isActive() && $payerFunds->lessThan($amount))
                    || ($payer->getType()->isPassive() && $payerFunds->greaterThan($amount))
                ) {
                    throw new InvalidArgumentException('Account is out of funds');
                }
            }
        }
    }

    private function createTransactionEntry(
        DateTimeImmutable $operationTime,
        Money $funds,
        Account $account,
        string $operationId
    ): TransactionEntryRecord {
        /** @var TransactionEntryRecord $record */
        $record = $this->factory->createAR(TransactionEntryRecord::class);
        $record->createdAt = $operationTime->getTimestamp();
        $record->currency = $funds->getCurrency()->getCode();
        $record->amount = (int) $funds->getAmount();
        $record->accountId = $account->getId();
        $record->operationId = $operationId;

        if (!$record->save()) {
            throw new RuntimeException('Cannot persist transaction');
        }

        return $record;
    }
}
