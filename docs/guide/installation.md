Installation
============

## Requirements

At least redis version 2.6.12 is required for all components to work properly.

## Getting Composer package

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```
composer require --prefer-dist yiisoft/yii2-redis
```

## Configuring application

To use this extension, you have to configure the [[yii\db\redis\Connection|Connection]] class in your application configuration:

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

This provides the basic access to redis storage via the `redis` application component:
 
```php
Yii::$app->redis->set('mykey', 'some value');
echo Yii::$app->redis->get('mykey');
```

See [[yii\db\redis\Connection]] for a full list of available methods.
