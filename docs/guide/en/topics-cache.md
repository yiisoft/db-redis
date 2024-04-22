Using the Cache component
=========================

To use the `Cache` component, in addition to configuring the connection as described in the [Installation](installation.md) section,
you also have to configure the `cache` component to be [[Yiisoft\Db\Redis\Cache]]:

```php
return [
    //....
    'components' => [
        // ...
        'cache' => [
            'class' => 'Yiisoft\Db\Redis\Cache',
        ],
    ]
];
```

If you only use the redis cache (i.e., not using its ActiveRecord or Session), you can also configure the parameters of the connection within the
cache component (no connection application component needs to be configured in this case):

```php
return [
    //....
    'components' => [
        // ...
        'cache' => [
            'class' => 'Yiisoft\Db\Redis\Cache',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 0,
            ],
        ],
    ]
];
```

The cache provides all methods of the [[yii\caching\CacheInterface]]. If you want to access redis specific methods that are not
included in the interface, you can use them via [[Yiisoft\Db\Redis\Cache::$redis]], which is an instance of [[Yiisoft\Db\Redis\Connection]]:

```php
Yii::$app->cache->redis->hset('mykey', 'somefield', 'somevalue');
Yii::$app->cache->redis->hget('mykey', 'somefield');
...
```

See [[Yiisoft\Db\Redis\Connection]] for a full list of available methods.
