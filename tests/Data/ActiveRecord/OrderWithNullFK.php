<?php

namespace Yiisoft\Db\Redis\Tests\Data\ActiveRecord;

use Yiisoft\Db\Redis\ActiveRecord;
use Yiisoft\ActiveRecord\BaseActiveRecord;

/**
 * Class Order
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $created_at
 * @property string $total
 */
class OrderWithNullFK extends ActiveRecord
{
    public function __construct()
    {
        BaseActiveRecord::connectionId('redis');
    }

    /**
     * @inheritdoc
     */
    public static function primaryKey(): array
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public function attributes(): array
    {
        return ['id', 'customer_id', 'created_at', 'total'];
    }
}
