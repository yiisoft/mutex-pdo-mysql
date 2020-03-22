<?php

namespace Yiisoft\Mutex;

/**
 * MysqlMutex implements mutex "lock" mechanism via MySQL locks.
 *
 * @see Mutex
 */
class MysqlMutex extends Mutex
{
    /**
     * @var \PDO
     */
    protected $connection;

    /**
     * DbMutex constructor.
     *
     * @param \PDO $connection
     * @param bool $autoRelease whether all locks acquired in this process (i.e. local locks) must be released
     *                          automatically before finishing script execution. Defaults to true. Setting this property
     *                          to true means that all locks acquired in this process must be released (regardless of
     *                          errors or exceptions).
     */
    public function __construct(\PDO $connection, bool $autoRelease = true)
    {
        $this->connection = $connection;
        $driverName = $connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
        if ($driverName !== 'mysql') {
            throw new \InvalidArgumentException('MySQL connection instance should be passed. Got ' . $driverName . '.');
        }

        parent::__construct($autoRelease);
    }

    /**
     * Acquires lock by given name.
     *
     * @param string $name    of the lock to be acquired.
     * @param int    $timeout time (in seconds) to wait for lock to become released.
     *
     * @return bool acquiring result.
     *
     * @see http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_get-lock
     */
    protected function acquireLock(string $name, int $timeout = 0): bool
    {
        $statement = $this->connection->prepare('SELECT GET_LOCK(:name, :timeout)');
        $statement->bindValue(':name', $this->hashLockName($name));
        $statement->bindValue(':timeout', $timeout);
        $statement->execute();

        return $statement->fetchColumn();
    }

    /**
     * Releases lock by given name.
     *
     * @param string $name of the lock to be released.
     *
     * @return bool release result.
     *
     * @see http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_release-lock
     */
    protected function releaseLock(string $name): bool
    {
        $statement = $this->connection->prepare('SELECT RELEASE_LOCK(:name)');
        $statement->bindValue(':name', $this->hashLockName($name));
        $statement->execute();

        return $statement->fetchColumn();
    }

    /**
     * Generate hash for lock name to avoid exceeding lock name length limit.
     *
     * @param string $name
     *
     * @return string
     *
     * @since 2.0.16
     * @see https://github.com/yiisoft/yii2/pull/16836
     */
    protected function hashLockName(string $name): string
    {
        return sha1($name);
    }
}
