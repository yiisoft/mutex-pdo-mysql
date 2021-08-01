<?php

declare(strict_types=1);

namespace Yiisoft\Mutex\Tests;

use Yiisoft\Mutex\MysqlMutex;

/**
 * Class MysqlMutexTest.
 *
 * @group mutex
 * @group db
 * @group mysql
 */
class MysqlMutexTest
{
    use MutexTestTrait;

    /**
     * @return MysqlMutex
     */
    protected function createMutex()
    {
        return new MysqlMutex('test', $this->getConnection());
    }

    private function getConnection(): PDO
    {
        // TODO: create MySQL connection here
    }
}
