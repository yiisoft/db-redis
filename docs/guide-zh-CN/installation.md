安装
============

## 需求

redis 2.6.12 版本是所有部件正常工作所必需的。

## 获取 Composer 安装包

安装此扩展的首选方式是通过 [composer](http://getcomposer.org/download/)。

可以运行

```
php composer.phar require --prefer-dist yiisoft/db-redis
```

或者添加

```json
"yiisoft/db-redis": "~1.0.0"
```

在 `composer.json` 文件中的必要部分。

## 配置应用程序

使用此扩展时，需要在你的应用程序配置中配置 [[Yiisoft\Db\Redis\Connection|Connection]] 类：

```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'Yiisoft\Db\Redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ]
];
```
