<?php

namespace Yiisoft\Db\Redis\Tests\Data\ActiveRecord;

use Yiisoft\Db\Redis\ActiveRecord;
use Yiisoft\ActiveRecord\BaseActiveRecord;

/**
 * Class Dummy
 */
class Dummy extends ActiveRecord
{
    public function __construct()
    {
        BaseActiveRecord::connectionId('redis');
    }
}
