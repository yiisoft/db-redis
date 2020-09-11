<?php

declare(strict_types=1);

namespace Yiisoft\Db\Redis;

use Yiisoft\Cache\Cache;

/**
 * Redis Cache implements a cache application component based on [redis](http://redis.io/) key-value store.
 *
 * Redis Cache requires redis version 2.6.12 or higher to work properly.
 *
 * It needs to be configured with a redis [[Connection]] that is also configured as an application component.
 * By default it will use the `redis` application component.
 *
 * See [[Cache]] manual for common cache operations that redis Cache supports.
 *
 * Unlike the [[Cache]], redis Cache allows the expire parameter of [[set]], [[add]], [[mset]] and [[madd]] to
 * be a floating point number, so you may specify the time in milliseconds (e.g. 0.1 will be 100 milliseconds).
 *
 * To use redis Cache as the cache application component, configure the application as follows,
 *
 * ~~~
 * [
 *     'cache' => [
 *         '__class' => \Yiisoft\Db\Redis\Cache::class,
 *         'redis' => [
 *             'hostname' => 'localhost',
 *             'port' => 6379,
 *             'database' => 0,
 *         ]
 *     ],
 * ]
 * ~~~
 *
 * Or if you have configured the redis [[Connection]] as an application component, the following is sufficient:
 *
 * ~~~
 * [
 *     'cache' => [
 *         '__class' => \Yiisoft\Db\Redis\Cache::class,
 *         // 'redis' => 'redis' // id of the connection application component
 *     ],
 * ]
 * ~~~
 *
 * If you have multiple redis replicas (e.g. AWS ElasticCache Redis) you can configure the cache to
 * send read operations to the replicas. If no replicas are configured, all operations will be performed on the
 * master connection configured via the [[redis]] property.
 *
 * ~~~
 * [
 *     'cache' => [
 *         '__class' => \Yiisoft\Db\Redis\Cache::class,
 *         'enableReplicas' => true,
 *         'replicas' => [
 *             // config for replica redis connections, (default class will be Yiisoft\Db\Redis\Connection if not provided)
 *             // you can optionally put in master as hostname as well, as all GET operation will use replicas
 *             'redis',//id of Redis [[Connection]] Component
 *             ['hostname' => 'redis-slave-002.xyz.0001.apse1.cache.amazonaws.com'],
 *             ['hostname' => 'redis-slave-003.xyz.0001.apse1.cache.amazonaws.com'],
 *         ],
 *     ],
 * ]
 * ~~~

 */
class Cache extends Cache
{
    /**
     * @var bool whether to enable read/get from redis replicas.
     *
     * {@see $replicas}
     */
    public bool $enableReplicas = false;

    /**
     * @var array the Redis [[Connection]] configurations for redis replicas.
     * Each entry is a class configuration, which will be used to instantiate a replica connection.
     * The default class is [[Connection|Yiisoft\Db\Redis\Connection]]. You should at least provide a hostname.
     *
     * Configuration example:
     *
     * ```php
     * 'replicas' => [
     *     'redis',
     *     ['hostname' => 'redis-slave-002.xyz.0001.apse1.cache.amazonaws.com'],
     *     ['hostname' => 'redis-slave-003.xyz.0001.apse1.cache.amazonaws.com'],
     * ],
     * ```
     *
     * {@see $enableReplicas}
     */
    public $replicas = [];

    /**
     * @var Connection currently active connection.
     */
    private $_replica;


    /**
     * Initializes the redis Cache component.
     * This method will initialize the [[redis]] property to make sure it refers to a valid redis connection.
     * @throws \yii\exceptions\InvalidConfigException if [[redis]] is invalid.
     */
    public function __construct(Connection $redis, $serializer = null)
    {
        parent::__construct($serializer);

        $this->redis = $redis;
    }

    /**
     * @inheritdoc
     */
    protected function getValue($key)
    {
        $value = $this->getReplica()->executeCommand('GET', [$key]);

        return $value ?? false;
    }

    /**
     * @inheritdoc
     */
    protected function getValues($keys): array
    {
        $response = $this->getReplica()->executeCommand('MGET', $keys);
        $result = [];
        $i = 0;
        foreach ($keys as $key) {
            $result[$key] = $response[$i++];
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function setValue($key, $value, $ttl): bool
    {
        if ($ttl == 0) {
            return (bool) $this->redis->executeCommand('SET', [$key, $value]);
        }

        $ttl = (int) ($ttl * 1000);

        return (bool) $this->redis->executeCommand('SET', [$key, $value, 'PX', $ttl]);
    }

    /**
     * @inheritdoc
     */
    protected function setValues($data, $ttl): bool
    {
        $args = [];
        foreach ($data as $key => $value) {
            $args[] = $key;
            $args[] = $value;
        }

        $failedKeys = [];
        if ($ttl == 0) {
            $this->redis->executeCommand('MSET', $args);
        } else {
            $ttl = (int) ($ttl * 1000);
            $this->redis->executeCommand('MULTI');
            $this->redis->executeCommand('MSET', $args);
            $index = [];
            foreach ($data as $key => $value) {
                $this->redis->executeCommand('PEXPIRE', [$key, $ttl]);
                $index[] = $key;
            }
            $result = $this->redis->executeCommand('EXEC');
            array_shift($result);
            foreach ($result as $i => $r) {
                if ($r != 1) {
                    $failedKeys[] = $index[$i];
                }
            }
        }

        // FIXME: Where do we access failed keys from ?
        return count($failedKeys) === 0;
    }

    /**
     * @inheritdoc
     */
    protected function addValue($key, $value, $ttl)
    {
        if ($ttl == 0) {
            return (bool) $this->redis->executeCommand('SET', [$key, $value, 'NX']);
        }

        $ttl = (int) ($ttl * 1000);

        return (bool) $this->redis->executeCommand('SET', [$key, $value, 'PX', $ttl, 'NX']);
    }

    /**
     * @inheritdoc
     */
    protected function deleteValue($key): bool
    {
        return (bool) $this->redis->executeCommand('DEL', [$key]);
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        return $this->redis->executeCommand('FLUSHDB');
    }

    /**
     * It will return the current Replica Redis [[Connection]], and fall back to default [[redis]] [[Connection]]
     * defined in this instance. Only used in getValue() and getValues().
     * @since 2.0.8
     * @return array|string|Connection
     * @throws \yii\exceptions\InvalidConfigException
     */
    protected function getReplica()
    {
        if ($this->enableReplicas === false) {
            return $this->redis;
        }

        if ($this->_replica !== null) {
            return $this->_replica;
        }

        if (empty($this->replicas)) {
            return $this->_replica = $this->redis;
        }

        $replicas = $this->replicas;
        shuffle($replicas);
        $config = array_shift($replicas);
        $this->_replica = Yii::ensureObject($config, Connection::class);
        return $this->_replica;
    }
}
