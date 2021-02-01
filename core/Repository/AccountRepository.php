<?php

declare(strict_types=1);

namespace YiiSoft\Billing\Repository;

use Money\Currency;
use Money\Money;
use YiiSoft\Billing\Entity\Account;

interface AccountRepository
{
    /**
     * Getting current account balance by the given currency
     *
     * @param Account $account
     * @param Currency $currency
     *
     * @return Money
     */
    public function getFunds(Account $account, Currency $currency): Money;
}
