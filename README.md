<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <a href="https://redis.io/" target="_blank" rel="external">
        <img src="https://download.redis.io/redis.png" height="80px">
    </a>
    <h1 align="center">Yii DBAL Redis connection</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/db-redis/v/stable.png)](https://packagist.org/packages/yiisoft/db-redis)
[![Total Downloads](https://poser.pugx.org/yiisoft/db-redis/downloads.png)](https://packagist.org/packages/yiisoft/db-redis)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/db-redis/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/db-redis/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/db-redis/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/db-redis/?branch=master)

This extension provides the [redis](https://redis.io/) connection support for the [Yii framework](https://www.yiiframework.com).

## Support version

| PHP | Redis Version | CI-Actions
|----|------------------------|---|
|**7.4 - 8.0**| **4 - 6**|[![Build status](https://github.com/yiisoft/db-redis/workflows/build/badge.svg)](https://github.com/yiisoft/db-redis/actions?query=workflow%3Abuild) [![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fdb-redis%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/db-redis/master) [![static analysis](https://github.com/yiisoft/db-redis/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/db-redis/actions?query=workflow%3A%22static+analysis%22) [![type-coverage](https://shepherd.dev/github/yiisoft/db-redis/coverage.svg)](https://shepherd.dev/github/yiisoft/db-redis)

## Installation

The package could be installed via composer:

```shell
composer require yiisoft/db-redis
```

## General usage

Using `yiisoft/composer-config-plugin` automatically get the settings of `EventDispatcherInterface::class` and `LoggerInterface::class`.

Di-Container:

```php
use Yiisoft\Db\Redis\Connection as RedisConnection;

return [
    RedisConnection::class => [
        '__class' => RedisConnection::class,
        'host()' => [$params['yiisoft/db-redis']['dsn']['host']],
        'port()' => [$params['yiisoft/db-redis']['dsn']['port']],
        'database()' => [$params['yiisoft/db-redis']['dsn']['database']],
        'password()' => [$params['yiisoft/db-redis']['password']]
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

## Documentation

- Guide: [English](docs/guide/en/README.md), [日本語](docs/guide/ja/README.md), [Português - Brasil](docs/guide/pt-BR/README.md), [Русский](docs/guide/ru/README.md), [中国人](docs/guide/zh-CN/README.md)
- [Internals](docs/internals.md)

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/63) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## License

The Yii DBAL Redis connection is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
