<?php

namespace yii\db\redis\tests\data\ar;

use yii\db\redis\ActiveQuery;

/**
 * CustomerQuery
 */
class CustomerQuery extends ActiveQuery
{
    /**
     * @return $this
     */
    public function active()
    {
        $this->andWhere(['status' => 1]);

        return $this;
    }
}
