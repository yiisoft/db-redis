<?php

namespace Yiisoft\Db\Redis\Tests\Data\ActiveRecord;

use Yiisoft\Db\Redis\ActiveRecord;
use Yiisoft\ActiveRecord\BaseActiveRecord;
use Yiisoft\Db\Redis\Tests\ActiveRecordTest;

/**
 * Customer
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $address
 * @property int $status
 *
 * @method CustomerQuery findBySql($sql, $params = []) static
 *
 * @property Order[] $orders
 * @property Order[] $expensiveOrders
 * @property Order[] $expensiveOrdersWithNullFK
 * @property Order[] $ordersWithNullFK
 * @property Order[] $ordersWithItems
 */
class Customer extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public $status2;

    public function __construct()
    {
        BaseActiveRecord::connectionId('redis');
    }

    /**
     * @inheritdoc
     */
    public function attributes(): array
    {
        return ['id', 'email', 'name', 'address', 'status', 'profile_id'];
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getExpensiveOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])->andWhere("tonumber(redis.call('HGET','order' .. ':a:' .. pk, 'total')) > 50");
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getExpensiveOrdersWithNullFK()
    {
        return $this->hasMany(OrderWithNullFK::class, ['customer_id' => 'id'])->andWhere("tonumber(redis.call('HGET','order' .. ':a:' .. pk, 'total')) > 50");
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getOrdersWithNullFK()
    {
        return $this->hasMany(OrderWithNullFK::class, ['customer_id' => 'id']);
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getOrdersWithItems()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])->with('orderItems');
    }

    /**
     * @return \Yiisoft\Db\Redis\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->via('orders');
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        ActiveRecordTest::$afterSaveInsert = $insert;
        ActiveRecordTest::$afterSaveNewRecord = $this->isNewRecord;
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     * @return CustomerQuery
     */
    public static function find()
    {
        return new CustomerQuery(get_called_class());
    }
}
