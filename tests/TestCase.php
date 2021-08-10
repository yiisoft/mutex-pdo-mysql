<?php

declare(strict_types=1);

namespace Yiisoft\Mutex\Mysql\Tests;

use PDO;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private ?PDO $connection = null;

    protected function tearDown(): void
    {
        $this->connection = null;

        parent::setUp();
    }

    protected function connection(): PDO
    {
        if ($this->connection === null) {
            $this->connection = new PDO(
                'mysql:host=127.0.0.1;dbname=yiitest',
                'root',
                'root-password',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
            );
        }

        return $this->connection;
    }

    protected function isFreeLock(string $name): bool
    {
        $statement = $this->connection()->prepare('SELECT IS_FREE_LOCK(:name)');
        $statement->bindValue(':name', sha1($name));
        $statement->execute();

        return (bool) $statement->fetchColumn();
    }
}
