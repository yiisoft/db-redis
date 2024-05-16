# Using the Session component

To use the [Session](https://github.com/yiisoft/session) component, in addition to configuring the connection as described in the [Configuring application](../../../README.md#Configuring-application) section,
you also have to configure the `session` component to be `Yiisoft\Db\Redis\Session`:

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => 'Yiisoft\Db\Redis\Session',
        ],
    ]
];
```

If you only use redis session (i.e., not using its [ActiveRecord](https://github.com/yiisoft/active-record) or [Cache](https://github.com/yiisoft/cache-redis)), you can also configure the parameters of the connection within the
session component (no connection application component needs to be configured in this case):

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => 'Yiisoft\Db\Redis\Session',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 0,
            ],
        ],
    ]
];
```
