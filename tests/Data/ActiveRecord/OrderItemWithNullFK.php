<?php

namespace Yiisoft\Db\Redis\Tests\Data\ActiveRecord;

use Yiisoft\Db\Redis\ActiveRecord;
use Yiisoft\ActiveRecord\BaseActiveRecord;

/**
 * Class OrderItem
 *
 * @property integer $order_id
 * @property integer $item_id
 * @property integer $quantity
 * @property string $subtotal
 */
class OrderItemWithNullFK extends ActiveRecord
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
        return ['order_id', 'item_id'];
    }

    /**
     * @inheritdoc
     */
    public function attributes(): array
    {
        return ['order_id', 'item_id', 'quantity', 'subtotal'];
    }
}
