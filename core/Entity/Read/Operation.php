<?php

declare(strict_types=1);

namespace YiiSoft\Billing\Entity\Read;

use YiiSoft\Billing\Entity\Transaction;

final class Operation
{
    /**
     * @var Transaction[]
     */
    private array $transactions;

    public function __construct(Transaction ...$transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }
}
