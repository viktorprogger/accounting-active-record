<?php

declare(strict_types=1);

namespace YiiSoft\Billing\Entity;

use Money\Money;

final class Funds
{
    private Account $account;
    private Money $amount;

    public function __construct(Account $account, Money $amount)
    {
        $this->account = $account;
        $this->amount = $amount;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }
}
