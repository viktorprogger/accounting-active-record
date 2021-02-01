<?php

declare(strict_types=1);

namespace YiiSoft\Billing\Entity\Write;

use Money\Money;
use YiiSoft\Billing\Entity\Account;
use YiiSoft\Billing\Entity\Transaction;

final class Operation
{
    /**
     * @var Transaction[]
     */
    private array $transactions = [];

    public function addTransaction(Money $funds, Account $payer, Account $recipient): void
    {
        $this->transactions[] = new Transaction($funds, $payer, $recipient);
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }
}
