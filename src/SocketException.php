<?php

declare(strict_types=1);

namespace Yiisoft\Db\Redis;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;

/**
 * SocketException indicates a socket connection failure in {@see ConnectionInterface}.
 */
class SocketException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName(): string
    {
        return 'Redis Socket Exception';
    }
}
