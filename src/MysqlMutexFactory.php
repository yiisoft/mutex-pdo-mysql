<?php

declare(strict_types=1);

namespace Yiisoft\Mutex\File;

use PDO;
use Yiisoft\Mutex\MutexFactoryInterface;
use Yiisoft\Mutex\MutexInterface;
use Yiisoft\Mutex\MysqlMutex;

/**
 * Allows creating {@see MysqlMutex} mutex objects.
 */
final class MysqlMutexFactory implements MutexFactoryInterface
{
    private PDO $connection;
    private bool $autoRelease;

    /**
     * @param PDO $connection PDO connection instance to use.
     * @param bool $autoRelease Whether to automatically release lock when PHP script ends.
     */
    public function __construct(PDO $connection, bool $autoRelease = true)
    {
        $this->connection = $connection;
        $this->autoRelease = $autoRelease;
    }

    public function create(string $name): MutexInterface
    {
        return new MysqlMutex($name, $this->connection, $this->autoRelease);
    }
}
