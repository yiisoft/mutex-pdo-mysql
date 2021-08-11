<?php

declare(strict_types=1);

namespace Yiisoft\Mutex\Mysql;

use InvalidArgumentException;
use PDO;
use RuntimeException;
use Yiisoft\Mutex\MutexInterface;
use Yiisoft\Mutex\RetryAcquireTrait;

use function sha1;

/**
 * MysqlMutex implements mutex "lock" mechanism via MySQL locks.
 */
final class MysqlMutex implements MutexInterface
{
    use RetryAcquireTrait;

    private string $name;
    private PDO $connection;

    /**
     * @param string $name Mutex name.
     * @param PDO $connection PDO connection instance to use.
     */
    public function __construct(string $name, PDO $connection)
    {
        $this->name = $name;
        $this->connection = $connection;

        /** @var string $driverName */
        $driverName = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driverName !== 'mysql') {
            throw new InvalidArgumentException("MySQL connection instance should be passed. Got $driverName.");
        }
    }

    public function __destruct()
    {
        $this->release();
    }

    /**
     * {@inheritdoc}
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/locking-functions.html#function_get-lock
     * @see https://dev.mysql.com/doc/refman/8.0/en/locking-functions.html#function_is-free-lock
     */
    public function acquire(int $timeout = 0): bool
    {
        $name = $this->hashLockName();

        return $this->retryAcquire($timeout, function () use ($name, $timeout): bool {
            if (!$this->isFreeLock()) {
                return false;
            }

            $statement = $this->connection->prepare('SELECT GET_LOCK(:name, :timeout)');
            $statement->bindValue(':name', $name);
            $statement->bindValue(':timeout', $timeout);
            $statement->execute();

            return (bool) $statement->fetchColumn();
        });
    }

    /**
     * {@inheritdoc}
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/locking-functions.html#function_release-lock
     * @see https://dev.mysql.com/doc/refman/8.0/en/locking-functions.html#function_is-free-lock
     */
    public function release(): void
    {
        if ($this->isFreeLock()) {
            return;
        }

        $statement = $this->connection->prepare('SELECT RELEASE_LOCK(:name)');
        $statement->bindValue(':name', $this->hashLockName());
        $statement->execute();

        if (!$statement->fetchColumn()) {
            throw new RuntimeException("Unable to release lock \"$this->name\".");
        }
    }

    /**
     * Generates hash for the lock name to avoid exceeding lock name length limit.
     *
     * @return string The generated hash for the lock name.
     *
     * @see https://github.com/yiisoft/yii2/pull/16836
     */
    private function hashLockName(): string
    {
        return sha1($this->name);
    }

    /**
     * Checks whether the lock is free to use (that is, not locked).
     *
     * @return bool Whether the lock is free to use.
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/locking-functions.html#function_is-free-lock
     */
    private function isFreeLock(): bool
    {
        $statement = $this->connection->prepare('SELECT IS_FREE_LOCK(:name)');
        $statement->bindValue(':name', $this->hashLockName());
        $statement->execute();

        return (bool) $statement->fetchColumn();
    }
}
