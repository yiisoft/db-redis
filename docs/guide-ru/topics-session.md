Использование компонента Session
================================

Чтобы использовать компонент `Session`, в дополнение к настройке соединения, как описано в разделе [Установка](installation.md), вам также нужно настроить компонент `session` как [[yii\db\redis\Session]]:

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => 'yii\db\redis\Session',
        ],
    ]
];
```

Если вы используете Redis только для хранения сессий (т.е. не используете его ActiveRecord или Cache), то в этом случае вы можете настроить параметры соединения в пределах компонента `session` следующим образом:

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => 'yii\db\redis\Session',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 0,
            ],
        ],
    ]
];
```
