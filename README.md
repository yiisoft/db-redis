<p align="center">
    <a href="http://redis.io/" target="_blank" rel="external">
        <img src="https://download.redis.io/redis.png" height="100px">
    </a>
    <h1 align="center">Yii DBAL Redis connection</h1>
    <br>
</p>

This extension provides the [redis](http://redis.io/) connection support for the [Yii framework](http://www.yiiframework.com).

[![Latest Stable Version](https://poser.pugx.org/yiisoft/db-redis/v/stable.png)](https://packagist.org/packages/yiisoft/db-redis)
[![Total Downloads](https://poser.pugx.org/yiisoft/db-redis/downloads.png)](https://packagist.org/packages/yiisoft/db-redis)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/db-redis/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/db-redis/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/db-redis/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/db-redis/?branch=master)


## Support version

|  PHP | Redis Version            |  CI-Actions
|:----:|:------------------------:|:---:|
|**7.4 - 8.0**| **4 - 6**|[![Build status](https://github.com/yiisoft/db-redis/workflows/build/badge.svg)](https://github.com/yiisoft/db-redis/actions?query=workflow%3Abuild) [![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fdb-redis%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/db-redis/master) [![static analysis](https://github.com/yiisoft/db-redis/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/db-redis/actions?query=workflow%3A%22static+analysis%22) [![type-coverage](https://shepherd.dev/github/yiisoft/db-redis/coverage.svg)](https://shepherd.dev/github/yiisoft/db-redis)


## Installation

The package could be installed via composer:

```php
composer require yiisoft/db-redis
```

## Configuration

Using `yiisoft/composer-config-plugin` automatically get the settings of `EventDispatcherInterface::class` and `LoggerInterface::class`.

Di-Container:

```php
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Db\Redis\Connection as RedisConnection;
use Yiisoft\Factory\Definitions\Reference;

return [
    RedisConnection::class => [
        '__class' => RedisConnection::class,
        '__construct()' => [
            Reference::to(EventDispatcherInterface::class),
            Reference::to(LoggerInterface::class),
        ],
        'hostname()' => [$params['yiisoft/db-redis']['dsn']['host']],
        'port()' => [$params['yiisoft/db-redis']['dsn']['port']],
        'database()' => [$params['yiisoft/db-redis']['database']],
        'password()' => [$params['yiisoft/db-redis']['password']],
    ]
];
```

Params.php

```php
return [
    'yiisoft/db-redis' => [
        'dsn' => [
            'driver' => 'redis',
            'host' => '127.0.0.1',
            'database' => 0,
            'port' => 6379
        ],
        'password' => null,
    ]
];
```

## Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```php
./vendor/bin/phpunit
```

Note: You must have REDIS installed to run the tests, it supports versions 4-6.

## Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```php
./vendor/bin/infection
```

## Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/docs/). To run static analysis:

```php
./vendor/bin/psalm
```
