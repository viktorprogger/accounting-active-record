<?php

declare(strict_types=1);

namespace YiiSoft\Billing\Entity;

use Money\Currencies;

final class Account
{
    private Currencies $currencies;
    private AccountType $type;
    private string $id;

    public function __construct(string $id, AccountType $type, Currencies $currencies)
    {
        $this->id = $id;
        $this->type = $type;
        $this->currencies = $currencies;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): AccountType
    {
        return $this->type;
    }

    public function getCurrenciesAvailable(): Currencies
    {
        return $this->currencies;
    }
}
