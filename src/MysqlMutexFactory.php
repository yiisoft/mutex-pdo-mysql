<?php

declare(strict_types=1);

namespace Yiisoft\Mutex\File;

use PDO;
use Yiisoft\Mutex\MutexFactory;
use Yiisoft\Mutex\MutexInterface;
use Yiisoft\Mutex\MysqlMutex;

/**
 * Allows creating {@see MysqlMutex} mutex objects.
 */
final class MysqlMutexFactory extends MutexFactory
{
    private PDO $connection;

    /**
     * @param PDO $connection PDO connection instance to use.
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(string $name): MutexInterface
    {
        return new MysqlMutex($name, $this->connection);
    }
}
