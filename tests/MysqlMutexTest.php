<?php

declare(strict_types=1);

namespace Yiisoft\Mutex\Mysql\Tests;

use InvalidArgumentException;
use PDO;
use Yiisoft\Mutex\Mysql\MysqlMutex;

use function microtime;

final class MysqlMutexTest extends TestCase
{
    public function testMutexAcquire(): void
    {
        $mutex = $this->createMutex('testMutexAcquire');

        $this->assertTrue($mutex->acquire());
        $mutex->release();
    }

    public function testThatMutexLockIsWorking(): void
    {
        $mutexOne = $this->createMutex('testThatMutexLockIsWorking');
        $mutexTwo = $this->createMutex('testThatMutexLockIsWorking');

        $this->assertTrue($mutexOne->acquire());
        $this->assertFalse($mutexTwo->acquire());
        $mutexOne->release();
        $mutexTwo->release();

        $this->assertTrue($mutexTwo->acquire());
        $mutexTwo->release();
    }

    public function testThatMutexLockIsWorkingOnTheSameComponent(): void
    {
        $mutex = $this->createMutex('testThatMutexLockIsWorkingOnTheSameComponent');

        $this->assertTrue($mutex->acquire());
        $this->assertFalse($mutex->acquire());

        $mutex->release();
        $mutex->release();
    }

    public function testTimeout(): void
    {
        $mutexName = __METHOD__;
        $mutexOne = $this->createMutex($mutexName);
        $mutexTwo = $this->createMutex($mutexName);

        $this->assertTrue($mutexOne->acquire());
        $microtime = microtime(true);
        $this->assertFalse($mutexTwo->acquire(1));
        $diff = microtime(true) - $microtime;
        $this->assertTrue($diff >= 1 && $diff < 2);
        $mutexOne->release();
        $mutexTwo->release();
    }

    public function testFreeLock(): void
    {
        $mutexName = 'testFreeLock';
        $mutex = $this->createMutex($mutexName);

        $mutex->acquire();
        $this->assertFalse($this->isFreeLock($mutex, $mutexName));

        $mutex->release();
        $this->assertTrue($this->isFreeLock($mutex, $mutexName));
    }

    public function testDestruct(): void
    {
        $mutexName = 'testDestruct';
        $mutex = $this->createMutex($mutexName);

        $this->assertTrue($mutex->acquire());
        $this->assertFalse($this->isFreeLock($mutex, $mutexName));

        unset($mutex);

        $statement = $this
            ->connection()
            ->prepare('SELECT RELEASE_LOCK(:name)');
        $statement->bindValue(':name', sha1($mutexName));
        $statement->execute();

        $this->assertFalse((bool) $statement->fetchColumn());
    }

    public function testConstructorFailure(): void
    {
        $connection = $this->createConfiguredMock(PDO::class, ['getAttribute' => 'pgsql']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('MySQL connection instance should be passed. Got "pgsql".');

        new MysqlMutex('testConstructorFailure', $connection);
    }

    private function createMutex(string $name): MysqlMutex
    {
        return new MysqlMutex($name, $this->connection());
    }
}
