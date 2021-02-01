<?php

declare(strict_types=1);

namespace YiiSoft\Billing\Entity;

use DateTimeImmutable;
use InvalidArgumentException;
use Money\Money;

final class Transaction
{
    private Money $funds;
    private Account $payer;
    private Account $recipient;
    private ?DateTimeImmutable $created;
    private ?string $id;

    public function __construct(
        Money $funds,
        Account $payer,
        Account $recipient,
        ?string $id = null,
        ?DateTimeImmutable $created = null
    ) {
        if (
            !$payer->getCurrenciesAvailable()->contains($funds->getCurrency())
            || !$recipient->getCurrenciesAvailable()->contains($funds->getCurrency())
        ) {
            throw new InvalidArgumentException('Both payer and recipient accounts must deal with the given currency.');
        }

        $this->funds = $funds;
        $this->payer = $payer;
        $this->recipient = $recipient;
        $this->id = $id;
        $this->created = $created;
    }

    public function getFunds(): Money
    {
        return $this->funds;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPayer(): Account
    {
        return $this->payer;
    }

    public function getRecipient(): Account
    {
        return $this->recipient;
    }

    public function getCreated(): ?DateTimeImmutable
    {
        return $this->created;
    }
}
