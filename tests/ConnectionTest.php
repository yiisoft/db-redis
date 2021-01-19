<?php

namespace Yiisoft\Db\Redis\Tests;

use Yiisoft\Log\Logger;
use Yiisoft\Db\Redis\Connection;
use Yiisoft\Db\Redis\SocketException;
use Yiisoft\Db\Redis\Tests\Event\AfterCustom;

/**
 * @group redis
 */
final class ConnectionTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * test connection to redis and selection of db
     */
    public function testConnect(): void
    {
        $db = $this->getConnection();

        $database = $db->getDatabase();

        $db->open();

        $this->assertTrue($db->ping());

        $db->set('YIITESTKEY', 'YIITESTVALUE');

        $db->close();

        $db = $this->getConnection();

        $db->database($database);

        $db->open();

        $this->assertEquals('YIITESTVALUE', $db->get('YIITESTKEY'));

        $db->close();

        $db = $this->getConnection();

        $db->database(1);

        $db->open();

        $this->assertNull($db->get('YIITESTKEY'));

        $db->close();
    }

    /**
     * tests whether close cleans up correctly so that a new connect works
     */
    public function testReConnect()
    {
        $db = $this->getConnection();

        $db->open();

        $this->assertTrue($db->ping());

        $db->close();

        $db->open();

        $this->assertTrue($db->ping());

        $db->close();
    }


    /**
     * @return array
     */
    public function keyValueData()
    {
        return [
            [123],
            [-123],
            [0],
            ['test'],
            ["test\r\ntest"],
            [''],
        ];
    }

    /**
     * @dataProvider keyValueData
     * @param mixed $data
     */
    public function testStoreGet($data)
    {
        $db = $this->getConnection(true);

        $db->set('hi', $data);
        $this->assertEquals($data, $db->get('hi'));
    }

    public function testSerialize()
    {
        $db = $this->getConnection();
        $db->open();
        $this->assertTrue($db->ping());
        $s = serialize($db);
        $this->assertTrue($db->ping());
        $db2 = unserialize($s);
        $this->assertTrue($db->ping());
        $this->assertTrue($db2->ping());
    }

    public function testConnectionTimeout()
    {
        $db = $this->getConnection();

        $db->configSet('timeout', 1);

        $this->assertTrue($db->ping());

        sleep(1);

        $this->assertTrue($db->ping());

        sleep(2);

        $this->expectException(SocketException::class);
        $this->assertTrue($db->ping());
    }

    public function testConnectionTimeoutRetry()
    {
        $db = $this->getConnection();

        $db->retries(1);

        $db->configSet('timeout', 1);

        $this->assertTrue($db->ping());

        $this->assertCount(
            4,
            $this->getInaccessibleProperty($this->logger, 'messages'),
            'log +1 ping command.'
        );

        usleep(500000); // 500ms

        $this->assertTrue($db->ping());
        $this->assertCount(
            5,
            $this->getInaccessibleProperty($this->logger, 'messages'),
            'log +1 ping command.'
        );

        sleep(2);

        /** reconnect should happen here */
        $this->assertTrue($db->ping());

        $this->assertCount(11, $this->getInaccessibleProperty($this->logger, 'messages'));
    }

    /**
     * Retry connecting 2 times
     */
    public function testConnectionTimeoutRetryCount()
    {
        $logger = $this->logger;

        $db = $this->getConnection();

        $db->retries(2);

        $db->configSet('timeout', 1);
        $db->runEvent(true);

        $this->assertCount(
            3,
            $this->getInaccessibleProperty($this->logger, 'messages'),
            'log of connection.'
        );

        $exception = false;

        try {
            /**
             * Should try to reconnect 2 times, before finally failing results in 3 times sending the PING command to redis.
             */
            sleep(2);

            $db->ping();
        } catch (SocketException $e) {
            $exception = true;
        }

        $this->assertTrue($exception, 'SocketException should have been thrown.');

        var_dump($this->getInaccessibleProperty($this->logger, 'messages'));
        die;
        $this->assertCount(14, $this->getInaccessibleProperty($this->logger, 'messages'));
    }

    /**
     * https://github.com/yiisoft/yii2/issues/4745
     */
    public function testReturnType()
    {
        $redis = $this->getConnection(true);

        $redis->executeCommand('SET', ['key1', 'val1']);
        $redis->executeCommand('HMSET', ['hash1', 'hk3', 'hv3', 'hk4', 'hv4']);
        $redis->executeCommand('RPUSH', ['newlist2', 'tgtgt', 'tgtt', '44', 11]);
        $redis->executeCommand('SADD', ['newset2', 'segtggttval', 'sv1', 'sv2', 'sv3']);
        $redis->executeCommand('ZADD', ['newz2', 2, 'ss', 3, 'pfpf']);
        $allKeys = $redis->executeCommand('KEYS', ['*']);

        sort($allKeys);

        $this->assertEquals(['hash1', 'key1', 'newlist2', 'newset2', 'newz2'], $allKeys);

        $expected = [
            'hash1' => 'hash',
            'key1' => 'string',
            'newlist2' => 'list',
            'newset2' => 'set',
            'newz2' => 'zset',
        ];

        foreach ($allKeys as $key) {
            $this->assertEquals($expected[$key], $redis->executeCommand('TYPE', [$key]));
        }
    }

    public function testTwoWordCommands()
    {
        $redis = $this->getConnection();
        $this->assertTrue(is_array($redis->executeCommand('CONFIG GET', ['port'])));
        $this->assertTrue(is_string($redis->clientList()));
        $this->assertTrue(is_string($redis->executeCommand('CLIENT LIST')));
    }
}
