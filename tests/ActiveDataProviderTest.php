<?php

namespace Yiisoft\Db\Redis\Tests;

use Yiisoft\Db\Redis\Tests\Data\ActiveRecord\Item;
use Yiisoft\ActiveRecord\ActiveDataProvider;

/**
 * @group redis
 */
class ActiveDataProviderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $item = new Item();
        $item->setAttributes(['name' => 'abc', 'category_id' => 1]);
        $item->save();

        $item = new Item();
        $item->setAttributes(['name' => 'def', 'category_id' => 2]);
        $item->save();
    }

    public function testQuery()
    {
        $query = Item::find();
        $provider = new ActiveDataProvider(Item::getConnection(), $query);
        $this->assertCount(2, $provider->getModels());

        $query = Item::find()->where(['category_id' => 1]);
        $provider = new ActiveDataProvider(Item::getConnection(), $query);
        $this->assertCount(1, $provider->getModels());
    }
}
