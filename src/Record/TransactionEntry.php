<?php

declare(strict_types=1);

namespace YiiSoft\Billing\ActiveRecord\Record;

use DateTime;
use Yiisoft\ActiveRecord\ActiveRecord;

/**
 * @property int $id
 * @property int $accountId
 * @property int $amount
 * @property string $currency
 * @property DateTime $createdAt
 */
class TransactionEntry extends ActiveRecord
{
    public function tableName(): string
    {
        return '{{%transaction_entry}}';
    }
}
