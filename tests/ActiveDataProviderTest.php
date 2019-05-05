<?php

namespace Yiisoft\Db\Redis\Tests;

use Yiisoft\Db\Redis\Tests\Data\ActiveRecord\ActiveRecord;
use Yiisoft\Db\Redis\Tests\Data\ActiveRecord\Item;
use Yiisoft\ActiveRecord\Data\ActiveDataProvider;

/**
 * @group redis
 */
class ActiveDataProviderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();

        $item = new Item();
        $item->setAttributes(['name' => 'abc', 'category_id' => 1], false);
        $item->save(false);
        $item = new Item();
        $item->setAttributes(['name' => 'def', 'category_id' => 2], false);
        $item->save(false);
    }

    public function testQuery()
    {
        $query = Item::find();
        $provider = new ActiveDataProvider(ActiveRecord::$db, $query);
        $this->assertCount(2, $provider->getModels());

        $query = Item::find()->where(['category_id' => 1]);
        $provider = new ActiveDataProvider(ActiveRecord::$db, $query);
        $this->assertCount(1, $provider->getModels());
    }
}
