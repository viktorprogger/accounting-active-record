<?php

declare(strict_types=1);

namespace YiiSoft\Billing\Entity;

use InvalidArgumentException;

final class AccountType
{
    public const ACTIVE = 1;
    public const PASSIVE = 2;
    public const ACTIVE_PASSIVE = 3;

    private int $current;

    public function __construct(int $current)
    {
        if (!in_array($current, [self::ACTIVE, self::PASSIVE, self::ACTIVE_PASSIVE], true)) {
            throw new InvalidArgumentException();
        }

        $this->current = $current;
    }

    public function current(): int
    {
        return $this->current;
    }

    public function isActive(): bool
    {
        return $this->current === self::ACTIVE;
    }

    public function isPassive(): bool
    {
        return $this->current === self::PASSIVE;
    }

    public function isActivePassive(): bool
    {
        return $this->current === self::ACTIVE_PASSIVE;
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function passive(): self
    {
        return new self(self::PASSIVE);
    }

    public static function activePassive(): self
    {
        return new self(self::ACTIVE_PASSIVE);
    }
}
