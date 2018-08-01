<?php

namespace yii\db\redis\tests\data\ar;

/**
 * ActiveRecord is ...
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveRecord extends \yii\db\redis\ActiveRecord
{
    /**
     * @return \yii\db\redis\Connection
     */
    public static $db;

    /**
     * @return \yii\db\redis\Connection
     */
    public static function getDb()
    {
        return self::$db;
    }
}
