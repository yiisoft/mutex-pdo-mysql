<?php

declare(strict_types=1);

namespace Yiisoft\Mutex\Mysql\Tests;

use PDO;
use ReflectionClass;
use Yiisoft\Mutex\Mysql\MysqlMutex;

use function md5;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private ?PDO $connection = null;

    protected function tearDown(): void
    {
        $this->connection = null;

        parent::setUp();
    }

    protected function connection(): PDO
    {
        if ($this->connection === null) {
            $this->connection = new PDO(
                'mysql:host=127.0.0.1;dbname=yiitest',
                'root',
                'root-password',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
            );
        }

        return $this->connection;
    }

    protected function isFreeLock(MysqlMutex $mutex, string $name): bool
    {
        $locks = (new ReflectionClass($mutex))->getParentClass()->getStaticPropertyValue('currentProcessLocks');

        return !isset($locks[md5(MysqlMutex::class . $name)]);
    }
}
