<?php

declare(strict_types=1);

namespace Yiisoft\Mutex;

use InvalidArgumentException;
use PDO;

/**
 * MysqlMutex implements mutex "lock" mechanism via MySQL locks.
 */
final class MysqlMutex implements MutexInterface
{
    private string $name;
    private PDO $connection;

    /**
     * DbMutex constructor.
     *
     * @param PDO $connection PDO connection instance to use.
     * @param bool $autoRelease Whether all locks acquired in this process (i.e. local locks) must be released
     * automatically before finishing script execution. Defaults to true. Setting this property
     * to true means that all locks acquired in this process must be released (regardless of
     * errors or exceptions).
     */
    public function __construct(string $name, PDO $connection, bool $autoRelease = true)
    {
        $this->name = $name;
        $this->connection = $connection;
        $driverName = $connection->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driverName !== 'mysql') {
            throw new InvalidArgumentException('MySQL connection instance should be passed. Got ' . $driverName . '.');
        }

        if ($autoRelease) {
            register_shutdown_function(function () {
                $this->release();
            });
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

        return $statement->fetchColumn();
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

        $statement->fetchColumn();
    }

    /**
     * Generate hash for lock name to avoid exceeding lock name length limit.
     *
     * @param string $name
     *
     * @return string
     * @see https://github.com/yiisoft/yii2/pull/16836
     */
    private function hashLockName(string $name): string
    {
        return sha1($name);
    }
}
