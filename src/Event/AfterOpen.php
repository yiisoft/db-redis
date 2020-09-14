<?php

declare(strict_types=1);

namespace Yiisoft\Db\Redis\Event;

final class AfterOpen
{
    public function getSleep(): void
    {
        sleep(4);
    }
}
