<?php

declare(strict_types=1);

namespace Bookkeeper\Accounting\ActiveRecord\Record;

use Yiisoft\ActiveRecord\ActiveRecord;

/**
 * @property int|null $id
 * @property int|null $createdAt
 */
final class OperationRecord extends ActiveRecord
{
    public function tableName(): string
    {
        return '{{%operation}}';
    }
}
