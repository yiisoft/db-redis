# Usando o componente Session

Para utilizar o componente [Session](https://github.com/yiisoft/session), além de configurar a conexão conforme descrito na seção  [Configurando o aplicativo](../../../README.md#Configuring-application),
você também deve configurar o componente `session` como `Yiisoft\Db\Redis\Session`:

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

Se você usar apenas sessão redis (ou seja, não usar [ActiveRecord](https://github.com/yiisoft/active-record) ou [Cache](https://github.com/yiisoft/cache-redis)), você também pode configurar os parâmetros da conexão dentro do
componente de sessão (nenhum componente de aplicativo de conexão precisa ser configurado neste caso):

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
