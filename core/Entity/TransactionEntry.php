<?php

declare(strict_types=1);

namespace YiiSoft\Billing\Entity;

use DateTimeImmutable;
use Money\Money;

final class TransactionEntry
{
    private Money $amount;
    private Account $account;
    private DateTimeImmutable $time;

    public function __construct(Money $amount, Account $account, DateTimeImmutable $time)
    {
        $this->amount = $amount;
        $this->account = $account;
        $this->time = $time;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getTime(): DateTimeImmutable
    {
        return $this->time;
    }
}
