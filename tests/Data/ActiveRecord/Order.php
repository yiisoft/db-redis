<?php

namespace Yiisoft\Db\Redis\Tests\Data\ActiveRecord;

use Yiisoft\Db\Redis\ActiveRecord;
use Yiisoft\ActiveRecord\BaseActiveRecord;

/**
 * Order
 *
 * @property int $id
 * @property int $customer_id
 * @property int $created_at
 * @property string $total
 *
 * @property Customer $customer
 * @property Item[] $itemsIndexed
 * @property OrderItem[] $orderItems
 * @property Item[] $items
 * @property Item[] $itemsInOrder1
 * @property Item[] $itemsInOrder2
 * @property Item[] $booksWithNullFK
 * @property Item[] $itemsWithNullFK
 * @property OrderItemWithNullFK[] $orderItemsWithNullFK
 * @property Item[] $books
 */
class Order extends ActiveRecord
{
    public function __construct()
    {
        BaseActiveRecord::connectionId('redis');
    }

    /**
     * @inheritdoc
     */
    public function attributes(): array
    {
        return ['id', 'customer_id', 'created_at', 'total'];
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->via('orderItems', function ($q) {
                // additional query configuration
            });
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getItemsIndexed()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->via('orderItems')->indexBy('id');
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getItemsWithNullFK()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->via('orderItemsWithNullFK');
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getOrderItemsWithNullFK()
    {
        return $this->hasMany(OrderItemWithNullFK::class, ['order_id' => 'id']);
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getItemsInOrder1()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->via('orderItems', function ($q) {
                $q->orderBy(['subtotal' => SORT_ASC]);
            })->orderBy('name');
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getItemsInOrder2()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->via('orderItems', function ($q) {
                $q->orderBy(['subtotal' => SORT_DESC]);
            })->orderBy('name');
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getBooks()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->via('orderItems')
            ->where(['category_id' => 1]);
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getBooksWithNullFK()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->via('orderItemsWithNullFK')
            ->where(['category_id' => 1]);
    }
}
