Использование компонента Cache
==============================

Чтобы использовать компонент `Cache`, в дополнение к настройке соединения, как описано в разделе [Установка](installation.md), вам также нужно настроить компонент `cache` как [[Yiisoft\Db\Redis\Cache]]:

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

Если вы используете Redis только для кеширования (т.е. не используете его ActiveRecord или Session), то в этом случае вы можете настроить параметры соединения в пределах компонента `cache` следующим образом:

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

Кэш предоставляет все методы [[yii\caching\CacheInterface]]. Если вы хотите получить доступ к определенным методам Redis, которые не присутствуют в интерфейсе, вы можете использовать их через [[Yiisoft\Db\Redis\Cache::$redis]], который является экземпляром [[Yiisoft\Db\Redis\Connection]]:

```php
Yii::$app->cache->redis->hset('mykey', 'somefield', 'somevalue');
Yii::$app->cache->redis->hget('mykey', 'somefield');
...
```

Смотри [[Yiisoft\Db\Redis\Connection]] для получения полного списка доступных методов.
