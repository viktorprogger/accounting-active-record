<?php

declare(strict_types=1);

namespace YiiSoft\Billing\Repository;

use InvalidArgumentException;
use YiiSoft\Billing\Entity\Read\Operation as OperationRead;
use YiiSoft\Billing\Entity\Write\Operation;

interface OperationRepositoryInterface
{
    /**
     * Atomic operation through all the given accounts
     *
     * @param Operation $operation The operation to be performed
     *
     * @throws InvalidArgumentException in the next cases:
     *      - The given operation is already performed
     *      - Transaction list is empty
     *      - One of accounts is out of funds (only active and passive accounts)
     *
     * @return OperationRead
     */
    public function perform(Operation $operation): OperationRead;
}
