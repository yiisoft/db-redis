Установка
============

## Требования

Для того, чтобы все компоненты работали должным образом, требуется хотя бы версия Redis 2.6.12.

## Установка расширения

Предпочтительным способом установки этого расширения является [Composer](http://getcomposer.org/download/).

Для этого запустите команду

```
php composer.phar require --prefer-dist yiisoft/db-redis
```

или добавьте

```json
"yiisoft/db-redis": "~1.0.0"
```

в секцию `require` вашего composer.json.

## Конфигурирование приложения

Чтобы использовать это расширение, вам необходимо настроить класс [[yii\db\redis\Connection|Connection]] в конфигурации вашего приложения:

```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'yii\db\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ]
];
```

Это обеспечивает базовый доступ к redis-хранилищу через компонент приложения `redis`:
 
```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

Смотри [[yii\db\redis\Connection]] для получения полного списка доступных методов.
