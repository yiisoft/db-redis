<?php

namespace Yiisoft\Db\Redis\Tests\Data\ActiveRecord;

use Yiisoft\Db\Redis\ActiveRecord;
use Yiisoft\ActiveRecord\BaseActiveRecord;

/**
 * Class OrderItem
 *
 * @property int $order_id
 * @property int $item_id
 * @property int $quantity
 * @property string $subtotal
 *
 * @property Order $order
 * @property Item $item
 */
class OrderItem extends ActiveRecord
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

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'item_id']);
    }
}
