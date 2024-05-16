# Usando o componente Cache

Para utilizar a [Caching Library](https://github.com/yiisoft/cache-redis), além de configurar a conexão conforme descrito na seção [Configurando o aplicativo](../../../README.md#Configuring-application),
você também deve configurar o componente `cache` como `Yiisoft\Db\Redis\Cache`:

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

Se você usar apenas o cache redis (ou seja, não usar seu [ActiveRecord](https://github.com/yiisoft/active-record) ou [Session](https://github.com/yiisoft/session)), você também pode configurar os parâmetros da conexão dentro do
componente de cache (nenhum componente de aplicativo de conexão precisa ser configurado neste caso):

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

O cache fornece todos os métodos de `yii\caching\CacheInterface`. Se você deseja acessar métodos específicos do Redis que não estão
incluídos na interface, você pode usá-los via `Yiisoft\Db\Redis\Cache::$redis`, que é uma instância de`Yiisoft\Db\Redis\Connection`:

```php
Yii::$app->cache->redis->hset('mykey', 'somefield', 'somevalue');
Yii::$app->cache->redis->hget('mykey', 'somefield');
...
```

Veja `Yiisoft\Db\Redis\Connection` para uma lista completa dos métodos disponíveis.
