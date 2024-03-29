<?php

namespace Luma\AuroraDatabase;

class DatabaseConnection
{
    protected \PDO $connection;

    /**
     * @param string $dsn
     * @param string|null $username
     * @param string|null $password
     * @param array $options
     *
     * @throws \Exception
     */
    public function __construct(string $dsn, ?string $username = null, ?string $password = null, array $options = [])
    {
        try {
            $this->connection = new \PDO($dsn, $username, $password, $options);
            $this->connection->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
            $this->connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_CLASS);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @return \PDO
     */
    public function getConnection(): \PDO
    {
        return $this->connection;
    }
}