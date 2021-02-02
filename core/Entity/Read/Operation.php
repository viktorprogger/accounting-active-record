<?php

declare(strict_types=1);

namespace YiiSoft\Billing\Entity\Read;

use YiiSoft\Billing\Entity\Transaction;

final class Operation
{
    private string $id;
    /**
     * @var Transaction[]
     */
    private array $transactions;

    public function __construct(string $id, Transaction ...$transactions)
    {
        $this->id = $id;
        $this->transactions = $transactions;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }
}
