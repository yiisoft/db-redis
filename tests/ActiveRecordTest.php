<?php

namespace Yiisoft\Db\Redis\Tests;

use Yiisoft\Db\Redis\ActiveQuery;
use Yiisoft\Db\Redis\LuaScriptBuilder;
use Yiisoft\Db\Redis\Tests\Data\ActiveRecord\ActiveRecord;
use Yiisoft\Db\Redis\Tests\Data\ActiveRecord\Customer;
use Yiisoft\Db\Redis\Tests\Data\ActiveRecord\Dummy;
use Yiisoft\Db\Redis\Tests\Data\ActiveRecord\OrderItem;
use Yiisoft\Db\Redis\Tests\Data\ActiveRecord\Order;
use Yiisoft\Db\Redis\Tests\Data\ActiveRecord\Item;
use Yiisoft\Db\Redis\Tests\Data\ActiveRecord\OrderItemWithNullFK;
use Yiisoft\Db\Redis\Tests\Data\ActiveRecord\OrderWithNullFK;
use Yiisoft\ActiveRecord\Tests\ActiveRecordTestTrait;

/**
 * @group redis
 */
class ActiveRecordTest extends TestCase
{
    //use ActiveRecordTestTrait;
    public function setUp(): void
    {
        parent::setUp();

        $this->getConnection(true);

        $customer = new Customer();

        $customer->setAttributes(['email' => 'user1@example.com', 'name' => 'user1', 'address' => 'address1', 'status' =>1, 'profile_id', 1]);
        $customer->save();
        $customer = new Customer();
        $customer->setAttributes(['email' => 'user2@example.com', 'name' => 'user2', 'address' => 'address2', 'status' => 1, 'profile_id' => null], false);
        $customer->save();
        $customer = new Customer();
        $customer->setAttributes(['email' => 'user3@example.com', 'name' => 'user3', 'address' => 'address3', 'status' => 2, 'profile_id' => 2], false);
        $customer->save();

        //		INSERT INTO category (name) VALUES ('Books');
        //		INSERT INTO category (name) VALUES ('Movies');

        $item = new Item();
        $item->setAttributes(['name' => 'Agile Web Application Development with Yii1.1 and PHP5', 'category_id' => 1], false);
        $item->save();
        $item = new Item();
        $item->setAttributes(['name' => 'Yii 1.1 Application Development Cookbook', 'category_id' => 1], false);
        $item->save();
        $item = new Item();
        $item->setAttributes(['name' => 'Ice Age', 'category_id' => 2], false);
        $item->save();
        $item = new Item();
        $item->setAttributes(['name' => 'Toy Story', 'category_id' => 2], false);
        $item->save();
        $item = new Item();
        $item->setAttributes(['name' => 'Cars', 'category_id' => 2], false);
        $item->save();

        $order = new Order();
        $order->setAttributes(['customer_id' => 1, 'created_at' => 1325282384, 'total' => 110.0], false);
        $order->save();
        $order = new Order();
        $order->setAttributes(['customer_id' => 2, 'created_at' => 1325334482, 'total' => 33.0], false);
        $order->save();
        $order = new Order();
        $order->setAttributes(['customer_id' => 2, 'created_at' => 1325502201, 'total' => 40.0], false);
        $order->save();

        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 1, 'subtotal' => 30.0], false);
        $orderItem->save();
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 1, 'item_id' => 2, 'quantity' => 2, 'subtotal' => 40.0], false);
        $orderItem->save();
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 4, 'quantity' => 1, 'subtotal' => 10.0], false);
        $orderItem->save();
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 5, 'quantity' => 1, 'subtotal' => 15.0], false);
        $orderItem->save();
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 3, 'quantity' => 1, 'subtotal' => 8.0], false);
        $orderItem->save();
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 3, 'item_id' => 2, 'quantity' => 1, 'subtotal' => 40.0], false);
        $orderItem->save();
        // insert a record with non-integer PK
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 3, 'item_id' => 'nostr', 'quantity' => 1, 'subtotal' => 40.0], false);
        $orderItem->save();

        $order = new OrderWithNullFK();
        $order->setAttributes(['customer_id' => 1, 'created_at' => 1325282384, 'total' => 110.0], false);
        $order->save();
        $order = new OrderWithNullFK();
        $order->setAttributes(['customer_id' => 2, 'created_at' => 1325334482, 'total' => 33.0], false);
        $order->save();
        $order = new OrderWithNullFK();
        $order->setAttributes(['customer_id' => 2, 'created_at' => 1325502201, 'total' => 40.0], false);
        $order->save();

        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 1, 'subtotal' => 30.0], false);
        $orderItem->save();
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 1, 'item_id' => 2, 'quantity' => 2, 'subtotal' => 40.0], false);
        $orderItem->save();
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 4, 'quantity' => 1, 'subtotal' => 10.0], false);
        $orderItem->save();
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 5, 'quantity' => 1, 'subtotal' => 15.0], false);
        $orderItem->save();
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 3, 'quantity' => 1, 'subtotal' => 8.0], false);
        $orderItem->save();
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 3, 'item_id' => 2, 'quantity' => 1, 'subtotal' => 40.0], false);
        $orderItem->save();
    }

    /**
     * overridden because null values are not part of the asArray result in redis
     */
    public function testFindAsArray()
    {
        /** asArray */
        $customer = Customer::find()->where(['id' => 2])->asArray()->one();

        $this->assertEquals([
            'id' => 2,
            'email' => 'user2@example.com',
            'name' => 'user2',
            'address' => 'address2',
            'status' => 1,
        ], $customer);

        /** find all asArray */
        $customers = Customer::find()->asArray()->all();

        $this->assertCount(3, $customers);
        $this->assertArrayHasKey('id', $customers[0]);
        $this->assertArrayHasKey('name', $customers[0]);
        $this->assertArrayHasKey('email', $customers[0]);
        $this->assertArrayHasKey('address', $customers[0]);
        $this->assertArrayHasKey('status', $customers[0]);
        $this->assertArrayHasKey('id', $customers[1]);
        $this->assertArrayHasKey('name', $customers[1]);
        $this->assertArrayHasKey('email', $customers[1]);
        $this->assertArrayHasKey('address', $customers[1]);
        $this->assertArrayHasKey('status', $customers[1]);
        $this->assertArrayHasKey('id', $customers[2]);
        $this->assertArrayHasKey('name', $customers[2]);
        $this->assertArrayHasKey('email', $customers[2]);
        $this->assertArrayHasKey('address', $customers[2]);
        $this->assertArrayHasKey('status', $customers[2]);
    }

    public function testStatisticalFind()
    {
        // find count, sum, average, min, max, scalar
        $this->assertEquals(3, Customer::find()->count());
        $this->assertEquals(6, Customer::find()->sum('id'));
        $this->assertEquals(2, Customer::find()->average('id'));
        $this->assertEquals(1, Customer::find()->min('id'));
        $this->assertEquals(3, Customer::find()->max('id'));

        $this->assertEquals(7, OrderItem::find()->count());
        $this->assertEquals(8, OrderItem::find()->sum('quantity'));
    }

    // TODO test serial column incr

    public function testUpdatePk()
    {
        // updateCounters
        $pk = ['order_id' => 2, 'item_id' => 4];
        /** @var OrderItem $orderItem */
        $orderItem = OrderItem::findOne($pk);
        $this->assertEquals(2, $orderItem->order_id);
        $this->assertEquals(4, $orderItem->item_id);

        $orderItem->order_id = 2;
        $orderItem->item_id = 10;
        $orderItem->save();

        $this->assertNull(OrderItem::findOne($pk));
        $this->assertNotNull(OrderItem::findOne(['order_id' => 2, 'item_id' => 10]));
    }

    public function testFilterWhere()
    {
        // should work with hash format
        $query = new ActiveQuery(Dummy::class);

        $query->filterWhere([
            'id' => 0,
            'title' => '   ',
            'author_ids' => [],
        ]);

        $this->assertEquals(['id' => 0], $query->getWhere());

        $query->andFilterWhere(['status' => null]);
        $this->assertEquals(['id' => 0], $query->getWhere());

        $query->orFilterWhere(['name' => '']);
        $this->assertEquals(['id' => 0], $query->getWhere());

        // should work with operator format
        $query = new ActiveQuery(Dummy::class);
        $condition = ['like', 'name', 'Alex'];
        $query->filterWhere($condition);
        $this->assertEquals($condition, $query->getWhere());

        $query->andFilterWhere(['between', 'id', null, null]);
        $this->assertEquals($condition, $query->getWhere());

        $query->orFilterWhere(['not between', 'id', null, null]);
        $this->assertEquals($condition, $query->getWhere());

        $query->andFilterWhere(['in', 'id', []]);
        $this->assertEquals($condition, $query->getWhere());

        $query->andFilterWhere(['not in', 'id', []]);
        $this->assertEquals($condition, $query->getWhere());

        $query->andFilterWhere(['not in', 'id', []]);
        $this->assertEquals($condition, $query->getWhere());

        $query->andFilterWhere(['like', 'id', '']);
        $this->assertEquals($condition, $query->getWhere());

        $query->andFilterWhere(['or like', 'id', '']);
        $this->assertEquals($condition, $query->getWhere());

        $query->andFilterWhere(['not like', 'id', '   ']);
        $this->assertEquals($condition, $query->getWhere());

        $query->andFilterWhere(['or not like', 'id', null]);
        $this->assertEquals($condition, $query->getWhere());
    }

    public function testFilterWhereRecursively()
    {
        $query = new ActiveQuery(Dummy::class);
        $query->filterWhere(['and', ['like', 'name', ''], ['like', 'title', ''], ['id' => 1], ['not', ['like', 'name', '']]]);
        $this->assertEquals(['and', ['id' => 1]], $query->getWhere());
    }

    public function testAutoIncrement()
    {
        Customer::getConnection()->executeCommand('FLUSHDB');

        $customer = new Customer();
        $customer->setAttributes(['id' => 4, 'email' => 'user4@example.com', 'name' => 'user4', 'address' => 'address4', 'status' => 1, 'profile_id' => null], false);
        $customer->save();
        $this->assertEquals(4, $customer->id);
        $customer = new Customer();
        $customer->setAttributes(['email' => 'user5@example.com', 'name' => 'user5', 'address' => 'address5', 'status' => 1, 'profile_id' => null], false);
        $customer->save();
        $this->assertEquals(5, $customer->id);

        $customer = new Customer();
        $customer->setAttributes(['id' => 1, 'email' => 'user1@example.com', 'name' => 'user1', 'address' => 'address1', 'status' => 1, 'profile_id' => null], false);
        $customer->save();
        $this->assertEquals(1, $customer->id);
        $customer = new Customer();
        $customer->setAttributes(['email' => 'user6@example.com', 'name' => 'user6', 'address' => 'address6', 'status' => 1, 'profile_id' => null], false);
        $customer->save();
        $this->assertEquals(6, $customer->id);


        /** @var Customer $customer */
        $customer = Customer::findOne(4);
        $this->assertNotNull($customer);
        $this->assertEquals('user4', $customer->name);

        $customer = Customer::findOne(5);
        $this->assertNotNull($customer);
        $this->assertEquals('user5', $customer->name);

        $customer = Customer::findOne(1);
        $this->assertNotNull($customer);
        $this->assertEquals('user1', $customer->name);

        $customer = Customer::findOne(6);
        $this->assertNotNull($customer);
        $this->assertEquals('user6', $customer->name);
    }

    public function testEscapeData()
    {
        $customer = new Customer();
        $customer->email = "the People's Republic of China";
        $customer->save();

        /** @var Customer $c */
        $c = Customer::findOne(['email' => "the People's Republic of China"]);
        $this->assertSame("the People's Republic of China", $c->email);
    }

    public function testFindEmptyWith()
    {
        Order::getConnection()->flushdb();

        $orders = Order::find()
            ->where(['total' => 100000])
            ->orWhere(['total' => 1])
            ->with('customer')
            ->all();

        $this->assertEquals([], $orders);
    }

    public function testEmulateExecution()
    {
        $rows = Order::find()
            ->emulateExecution()
            ->all();
        $this->assertSame([], $rows);

        $row = Order::find()
            ->emulateExecution()
            ->one();
        $this->assertSame(null, $row);

        $exists = Order::find()
            ->emulateExecution()
            ->exists();
        $this->assertSame(false, $exists);

        $count = Order::find()
            ->emulateExecution()
            ->count();
        $this->assertSame(0, $count);

        $sum = Order::find()
            ->emulateExecution()
            ->sum('id');
        $this->assertSame(0, $sum);

        $sum = Order::find()
            ->emulateExecution()
            ->average('id');
        $this->assertSame(0, $sum);

        $max = Order::find()
            ->emulateExecution()
            ->max('id');
        $this->assertSame(null, $max);

        $min = Order::find()
            ->emulateExecution()
            ->min('id');
        $this->assertSame(null, $min);

        $scalar = Order::find()
            ->emulateExecution()
            ->scalar('id');
        $this->assertSame(null, $scalar);

        $column = Order::find()
            ->emulateExecution()
            ->column('id');
        $this->assertSame([], $column);
    }

    /**
     * @see https://github.com/yiisoft/yii2-redis/issues/93
     */
    public function testDeleteAllWithCondition()
    {
        $deletedCount = Order::deleteAll(['in', 'id', [1, 2, 3]]);
        $this->assertEquals(3, $deletedCount);
    }

    public function testBuildKey()
    {
        $pk = ['order_id' => 3, 'item_id' => 'nostr'];
        $key = OrderItem::buildKey($pk);

        $orderItem = OrderItem::findOne($pk);
        $this->assertNotNull($orderItem);

        $pk = ['order_id' => $orderItem->order_id, 'item_id' => $orderItem->item_id];
        $this->assertEquals($key, OrderItem::buildKey($pk));
    }

    public function testNotCondition()
    {
        /* @var $orderClass \Yiisoft\Db\ActiveRecordInterface */
        $orderClass = Order::class;

        /* @var $this TestCase|ActiveRecordTestTrait */
        $orders = $orderClass::find()->where(['not', ['customer_id' => 2]])->all();
        $this->assertCount(1, $orders);
        $this->assertEquals(1, $orders[0]['customer_id']);
    }


    public function testBetweenCondition()
    {
        /* @var $orderClass \Yiisoft\Db\ActiveRecordInterface */
        $orderClass = Order::class;

        /* @var $this TestCase|ActiveRecordTestTrait */
        $orders = $orderClass::find()->where(['between', 'total', 30, 50])->all();
        $this->assertCount(2, $orders);
        $this->assertEquals(2, $orders[0]['customer_id']);
        $this->assertEquals(2, $orders[1]['customer_id']);

        $orders = $orderClass::find()->where(['not between', 'total', 30, 50])->all();
        $this->assertCount(1, $orders);
        $this->assertEquals(1, $orders[0]['customer_id']);
    }

    public function testInCondition()
    {
        /* @var $orderClass \Yiisoft\Db\ActiveRecordInterface */
        $orderClass = Order::class;

        /* @var $this TestCase|ActiveRecordTestTrait */
        $orders = $orderClass::find()->where(['in', 'customer_id', [1, 2]])->all();
        $this->assertCount(3, $orders);

        $orders = $orderClass::find()->where(['not in', 'customer_id', [1, 2]])->all();
        $this->assertCount(0, $orders);

        $orders = $orderClass::find()->where(['in', 'customer_id', [1]])->all();
        $this->assertCount(1, $orders);
        $this->assertEquals(1, $orders[0]['customer_id']);

        $orders = $orderClass::find()->where(['in', 'customer_id', [2]])->all();
        $this->assertCount(2, $orders);
        $this->assertEquals(2, $orders[0]['customer_id']);
        $this->assertEquals(2, $orders[1]['customer_id']);
    }

    public function testCountQuery()
    {
        /* @var $itemClass \Yiisoft\Db\ActiveRecordInterface */
        $itemClass = Item::class;

        $query = $itemClass::find();
        $this->assertEquals(5, $query->count());

        $query = $itemClass::find()->where(['category_id' => 1]);
        $this->assertEquals(2, $query->count());

        // negative values deactivate limit and offset (in case they were set before)
        $query = $itemClass::find()->where(['category_id' => 1])->limit(-1)->offset(-1);
        $this->assertEquals(2, $query->count());
    }

    public function illegalValuesForWhere()
    {
        return [
            [['id' => ["' .. redis.call('FLUSHALL') .. '" => 1]], ["'\\' .. redis.call(\\'FLUSHALL\\') .. \\'", 'rediscallFLUSHALL']],
            [['id' => ['`id`=`id` and 1' => 1]], ["'`id`=`id` and 1'", 'ididand']],
            [['id' => [
                'legal' => 1,
                '`id`=`id` and 1' => 1,
            ]], ["'`id`=`id` and 1'", 'ididand']],
            [['id' => [
                'nested_illegal' => [
                    'false or 1=' => 1
                ]
            ]], [], ['false or 1=']],
        ];
    }

    /**
     * @dataProvider illegalValuesForWhere
     */
    public function testValueEscapingInWhere($filterWithInjection, $expectedStrings, $unexpectedStrings = [])
    {
        /* @var $itemClass \Yiisoft\Db\ActiveRecordInterface */
        $itemClass = Item::class;

        $query = $itemClass::find()->where($filterWithInjection['id']);
        $lua = new LuaScriptBuilder();
        $script = $lua->buildOne($query);

        foreach ($expectedStrings as $string) {
            $this->assertStringContainsString($string, $script);
        }

        foreach ($unexpectedStrings as $string) {
            $this->assertStringNotContainsString($string, $script);
        }
    }

    public function illegalValuesForFindByCondition()
    {
        return [
            // code injection
            [['id' => ["' .. redis.call('FLUSHALL') .. '" => 1]], ["'\\' .. redis.call(\\'FLUSHALL\\') .. \\'", 'rediscallFLUSHALL'], ["' .. redis.call('FLUSHALL') .. '"]],
            [['id' => ['`id`=`id` and 1' => 1]], ["'`id`=`id` and 1'", 'ididand']],
            [['id' => [
                'legal' => 1,
                '`id`=`id` and 1' => 1,
            ]], ["'`id`=`id` and 1'", 'ididand']],
            [['id' => [
                'nested_illegal' => [
                    'false or 1=' => 1
                ]
            ]], [], ['false or 1=']],

            // custom condition injection
            [['id' => [
                'or',
                '1=1',
                'id' => 'id',
            ]], ["cid0=='or' or cid0=='1=1' or cid0=='id'"], []],
            [['id' => [
                0 => 'or',
                'first' => '1=1',
                'second' => 1,
            ]], ["cid0=='or' or cid0=='1=1' or cid0=='1'"], []],
            [['id' => [
                'name' => 'test',
                'email' => 'test@example.com',
                "' .. redis.call('FLUSHALL') .. '" => "' .. redis.call('FLUSHALL') .. '"
            ]], ["'\\' .. redis.call(\\'FLUSHALL\\') .. \\'", 'rediscallFLUSHALL'], ["' .. redis.call('FLUSHALL') .. '"]],
        ];
    }

    /**
     * @dataProvider illegalValuesForFindByCondition
     */
    public function testValueEscapingInFindByCondition($filterWithInjection, $expectedStrings, $unexpectedStrings = [])
    {
        /* @var $itemClass \Yiisoft\Db\ActiveRecordInterface */
        $itemClass = Item::class;

        $query = $this->invokeMethod(new $itemClass, 'findByCondition', [$filterWithInjection['id']]);
        $lua = new LuaScriptBuilder();
        $script = $lua->buildOne($query);

        foreach ($expectedStrings as $string) {
            $this->assertStringContainsString($string, $script);
        }
        foreach ($unexpectedStrings as $string) {
            $this->assertStringNotContainsString($string, $script);
        }
        // ensure injected FLUSHALL call did not succeed
        $query->one();
        $this->assertGreaterThan(3, $itemClass::find()->count());
    }
}
