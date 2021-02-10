<?php

declare(strict_types=1);

namespace Bookkeeper\Accounting\ActiveRecord\Record;

use Yiisoft\ActiveRecord\ActiveRecord;

/**
 * @property int $id
 * @property string $accountId
 * @property int $amount
 * @property string $currency
 * @property int $createdAt
 * @property string $operationId
 */
final class TransactionEntryRecord extends ActiveRecord
{
    public function tableName(): string
    {
        return '{{%transaction_entry}}';
    }
}
