<?php

namespace Yiisoft\Db\Redis\Tests\Data\ActiveRecord;

/**
 * ActiveRecord is ...
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveRecord extends \Yiisoft\Db\Redis\ActiveRecord
{
    /**
     * @return \Yiisoft\Db\Redis\Connection
     */
    public static $db;

    /**
     * @return \Yiisoft\Db\Redis\Connection
     */
    public static function getDb()
    {
        return self::$db;
    }
}
