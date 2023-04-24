<?php

declare(strict_types=1);

namespace Yiisoft\Mutex\Mysql;

use PDO;
use Yiisoft\Mutex\MutexFactory;
use Yiisoft\Mutex\MutexInterface;

/**
 * Allows creating {@see MysqlMutex} mutex objects.
 */
final class MysqlMutexFactory extends MutexFactory
{
    /**
     * @param PDO $connection PDO connection instance to use.
     */
    public function __construct(private PDO $connection)
    {
    }

    public function create(string $name): MutexInterface
    {
        return new MysqlMutex($name, $this->connection);
    }
}
