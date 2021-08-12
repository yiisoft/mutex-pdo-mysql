<?php

declare(strict_types=1);

namespace Yiisoft\Mutex\Mysql;

use InvalidArgumentException;
use PDO;
use Yiisoft\Mutex\Mutex;

use function sha1;

/**
 * MysqlMutex implements mutex "lock" mechanism via MySQL locks.
 */
final class MysqlMutex extends Mutex
{
    private string $lockName;
    private PDO $connection;

    /**
     * @param string $name Mutex name.
     * @param PDO $connection PDO connection instance to use.
     */
    public function __construct(string $name, PDO $connection)
    {
        $this->lockName = sha1($name);
        $this->connection = $connection;

        /** @var string $driverName */
        $driverName = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driverName !== 'mysql') {
            throw new InvalidArgumentException("MySQL connection instance should be passed. Got \"$driverName\".");
        }

        parent::__construct(self::class, $name);
    }

    /**
     * {@inheritdoc}
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/locking-functions.html#function_get-lock
     * @see https://dev.mysql.com/doc/refman/8.0/en/locking-functions.html#function_is-free-lock
     */
    public function acquireLock(int $timeout = 0): bool
    {
        $statement = $this->connection->prepare('SELECT GET_LOCK(:name, :timeout)');
        $statement->bindValue(':name', $this->lockName);
        $statement->bindValue(':timeout', $timeout);
        $statement->execute();

        return (bool) $statement->fetchColumn();
    }

    /**
     * {@inheritdoc}
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/locking-functions.html#function_release-lock
     * @see https://dev.mysql.com/doc/refman/8.0/en/locking-functions.html#function_is-free-lock
     */
    public function releaseLock(): bool
    {
        $statement = $this->connection->prepare('SELECT RELEASE_LOCK(:name)');
        $statement->bindValue(':name', $this->lockName);
        $statement->execute();

        return (bool) $statement->fetchColumn();
    }
}
