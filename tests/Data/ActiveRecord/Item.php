<?php

namespace Yiisoft\Db\Redis\Tests\Data\ActiveRecord;

use Yiisoft\Db\Redis\ActiveRecord;
use Yiisoft\ActiveRecord\BaseActiveRecord;

/**
 * Class Item
 *
 * @property int $id
 * @property string $name
 * @property int $category_id
 */
class Item extends ActiveRecord
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
        return ['id', 'name', 'category_id'];
    }
}
