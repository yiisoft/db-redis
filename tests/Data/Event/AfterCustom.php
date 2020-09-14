<?php

declare(strict_types=1);

namespace Yiisoft\Db\Redis\Tests\Data\Event;

use Yiisoft\Db\Redis\Connection;

final class AfterCustom
{
    public function getSleep(): void
    {
        sleep(2);
    }
}
