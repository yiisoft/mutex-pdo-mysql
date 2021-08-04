<?php

declare(strict_types=1);

namespace Yiisoft\Mutex\Tests;

use Yiisoft\Mutex\MutexInterface;
use Yiisoft\Mutex\MysqlMutex;

class MysqlMutexTest
{
    use MutexTestTrait;

    /**
     * @return MysqlMutex
     */
    protected function createMutex(): MutexInterface
    {
        return new MysqlMutex('test', $this->getConnection());
    }

    private function getConnection(): PDO
    {
        // TODO: create MySQL connection here
    }
}
