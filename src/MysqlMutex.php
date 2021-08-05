<?php

declare(strict_types=1);

namespace Yiisoft\Mutex;

use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * MysqlMutex implements mutex "lock" mechanism via MySQL locks.
 */
final class MysqlMutex extends Mutex
{
    private string $name;
    private PDO $connection;
    private bool $released = false;

    /**
     * DbMutex constructor.
     *
     * @param string $name Mutex name.
     * @param PDO $connection PDO connection instance to use.
     */
    public function __construct(string $name, PDO $connection)
    {
        $this->name = $name;
        $this->connection = $connection;
        $driverName = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driverName !== 'mysql') {
            throw new InvalidArgumentException('MySQL connection instance should be passed. Got ' . $driverName . '.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_get-lock
     */
    public function acquire(int $timeout = 0): bool
    {
        $statement = $this->connection->prepare('SELECT GET_LOCK(:name, :timeout)');
        $statement->bindValue(':name', $this->hashLockName($this->name));
        $statement->bindValue(':timeout', $timeout);
        $statement->execute();
        
        if ($statement->fetchColumn()) {
            $this->released = false;
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @see http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_release-lock
     */
    public function release(): void
    {
        $statement = $this->connection->prepare('SELECT RELEASE_LOCK(:name)');
        $statement->bindValue(':name', $this->hashLockName($this->name));
        $statement->execute();

        if (!$statement->fetchColumn()) {
            throw new RuntimeException("Unable to release lock \"$this->name\".");
        }
        
        $this->released = true;
    }
    
    public function isReleased(): bool
    {
        return $this->released;
    }

    /**
     * Generate hash for lock name to avoid exceeding lock name length limit.
     *
     * @param string $name
     *
     * @return string
     *
     * @see https://github.com/yiisoft/yii2/pull/16836
     */
    private function hashLockName(string $name): string
    {
        return sha1($name);
    }
}
