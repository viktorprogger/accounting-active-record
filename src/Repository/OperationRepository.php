<?php

declare(strict_types=1);

namespace YiiSoft\Billing\ActiveRecord\Repository;

use DateTimeImmutable;
use InvalidArgumentException;
use Money\Money;
use RuntimeException;
use Throwable;
use Yiisoft\ActiveRecord\ActiveRecordFactory;
use YiiSoft\Billing\ActiveRecord\Record\TransactionEntry;
use YiiSoft\Billing\Entity\Account;
use YiiSoft\Billing\Entity\Read\Operation as OperationRead;
use YiiSoft\Billing\Entity\Transaction;
use YiiSoft\Billing\Entity\Write\Operation;
use YiiSoft\Billing\Repository\AccountRepository;
use YiiSoft\Billing\Repository\OperationRepositoryInterface;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Connection\ConnectionInterface;

class OperationRepository implements OperationRepositoryInterface
{
    private AccountRepository $accountRepository;
    private ActiveRecordFactory $factory;
    private ConnectionInterface $connection;

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

        try {
            $this->checkOutOfFunds($operation);

            // TODO Create operation record here to set its id to transactions

            $transactions = [];
            $operationTime = new DateTimeImmutable();

            foreach ($operation->getTransactions() as $transaction) {
                $recordPayer = $this->createTransactionEntry($operationTime, $transaction);
                $recordRecipient = $this->createTransactionEntry($operationTime, $transaction);

                $transactions[] = new Transaction(
                    $transaction->getFunds(),
                    $transaction->getPayer(),
                    $transaction->getRecipient(),
                    "$recordPayer->id-$recordRecipient->id",
                    $operationTime,
                );
            }

            // TODO commit transaction and return
        } catch (Throwable $exception) {
            $dbTransaction->rollBack();

            throw $exception;
        }
    }

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
    ): TransactionEntry {
        /** @var TransactionEntry $record */
        $record = $this->factory->createAR(TransactionEntry::class);
        $record->createdAt = $operationTime;
        $record->currency = $funds->getCurrency()->getCode();
        $record->amount = (int) $funds->getAmount();
        $record->accountId = $account->getId();

        if ($record->save()) {
            throw new RuntimeException('Cannot persist transaction');
        }

        return $record;
    }
}
