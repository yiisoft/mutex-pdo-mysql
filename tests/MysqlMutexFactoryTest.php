<?php

declare(strict_types=1);

namespace Yiisoft\Mutex\Mysql\Tests;

use Yiisoft\Mutex\MutexInterface;
use Yiisoft\Mutex\Mysql\MysqlMutex;
use Yiisoft\Mutex\Mysql\MysqlMutexFactory;

final class MysqlMutexFactoryTest extends TestCase
{
    public function testCreateAndAcquire(): void
    {
        $mutexName = 'testCreateAndAcquire';
        $factory = new MysqlMutexFactory($this->connection());
        $mutex = $factory->createAndAcquire($mutexName);

        $this->assertInstanceOf(MutexInterface::class, $mutex);
        $this->assertInstanceOf(MysqlMutex::class, $mutex);

        $this->assertFalse($this->isFreeLock($mutexName));
        $this->assertFalse($mutex->acquire());
        $mutex->release();

        $this->assertTrue($this->isFreeLock($mutexName));
        $this->assertTrue($mutex->acquire());
        $this->assertFalse($this->isFreeLock($mutexName));

        $mutex->release();
        $this->assertTrue($this->isFreeLock($mutexName));
    }
}
