<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px" alt="Yii">
    </a>
    <h1 align="center">Yii Mutex Library - MySQL PDO Driver</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/mutex-pdo-mysql/v)](https://packagist.org/packages/yiisoft/mutex-pdo-mysql)
[![Total Downloads](https://poser.pugx.org/yiisoft/mutex-pdo-mysql/downloads)](https://packagist.org/packages/yiisoft/mutex-pdo-mysql)
[![Build status](https://github.com/yiisoft/mutex-pdo-mysql/actions/workflows/build.yml/badge.svg)](https://github.com/yiisoft/mutex-pdo-mysql/actions/workflows/build.yml)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/mutex-pdo-mysql/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/mutex-pdo-mysql/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/mutex-pdo-mysql/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/mutex-pdo-mysql/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fmutex-pdo-mysql%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/mutex-pdo-mysql/master)
[![static analysis](https://github.com/yiisoft/mutex-pdo-mysql/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/mutex-pdo-mysql/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/mutex-pdo-mysql/coverage.svg)](https://shepherd.dev/github/yiisoft/mutex-pdo-mysql)

This library provides a MySQL mutex implementation for [yiisoft/mutex](https://github.com/yiisoft/mutex).

## Requirements

- PHP 7.4 or higher.
- `PDO` PHP extension.

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```shell
composer require yiisoft/mutex-pdo-mysql
```

## General usage

The package provides two classes implementing `MutexInterface` and `MutexFactoryInterface`
from the [yiisoft/mutex](https://github.com/yiisoft/mutex) package:

```php
/**
 * @var \PDO $connection Configured for MySQL.
 */

$mutex = new \Yiisoft\Mutex\Mysql\MysqlMutex('mutex-name', $connection);

$mutexFactory = new \Yiisoft\Mutex\Mysql\MysqlMutexFactory($connection);
```

There are multiple ways you can use the package. You can execute a callback in a synchronized mode i.e. only a
single instance of the callback is executed at the same time:

```php
$synchronizer = new \Yiisoft\Mutex\Synchronizer($mutexFactory);

$newCount = $synchronizer->execute('critical', function () {
    return $counter->increase();
}, 10);
```

Another way is to manually open and close mutex:

```php
$simpleMutex = \Yiisoft\Mutex\SimpleMutex($mutexFactory);

if (!$simpleMutex->acquire('critical', 10)) {
    throw new \Yiisoft\Mutex\Exception\MutexLockedException('Unable to acquire the "critical" mutex.');
}

$newCount = $counter->increase();
$simpleMutex->release('critical');
```

It could be done on lower level:

```php
$mutex = $mutexFactory->createAndAcquire('critical', 10);
$newCount = $counter->increase();
$mutex->release();
```

And if you want even more control, you can acquire mutex manually:

```php
$mutex = $mutexFactory->create('critical');

if (!$mutex->acquire(10)) {
    throw new \Yiisoft\Mutex\Exception\MutexLockedException('Unable to acquire the "critical" mutex.');
}

$newCount = $counter->increase();
$mutex->release();
```

The `MysqlMutex` supports the "wait for a lock for a certain time" functionality. Using the `withRetryDelay()`
method, you can override the number of milliseconds between each try until specified timeout times out:

```php
$mutex = $mutex->withRetryDelay(100);
```

By default, it is 50 milliseconds - it means that we may try to acquire lock up to 20 times per second.

## Documentation

- [Internals](docs/internals.md)

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/63) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## License

The Yii Mutex Library - MySQL PDO Driver is free software. It is released under the terms of the BSD License.
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
