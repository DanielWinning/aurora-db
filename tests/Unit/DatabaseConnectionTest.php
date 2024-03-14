<?php

namespace Luma\Tests\Unit;

use Luma\DatabaseComponent\DatabaseConnection;
use PHPUnit\Framework\TestCase;

class DatabaseConnectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testItConnectsToDatabase(): void
    {
        $connection = $this->getTestClass();

        $this->assertInstanceOf(DatabaseConnection::class, $connection);
    }

    /**
     * @return void
     */
    public function testItCreatesPDOConnection(): void
    {
        $connection = $this->getTestClass();

        $this->assertInstanceOf(\PDO::class, $connection->getConnection());
        $this->assertEquals(
            \PDO::FETCH_CLASS,
            $connection->getConnection()->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE)
        );
    }

    /**
     * @return void
     */
    public function testItThrowsAnExceptionIfProvidedInvalidCredentials(): void
    {
        $this->expectException(\Exception::class);

        new DatabaseConnection(
            'mysql:host=0.0.0.0;port=3306',
            'root',
            ''
        );
    }

    /**
     * @return DatabaseConnection
     */
    private function getTestClass(): DatabaseConnection
    {
        // @todo Refactor this so it doesn't just work on my machine
        return new DatabaseConnection(
            'mysql:host=localhost;port=19909;',
            'root',
            'docker'
        );
    }
}