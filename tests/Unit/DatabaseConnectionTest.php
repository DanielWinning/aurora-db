<?php

namespace Luma\Tests\Unit;

use Dotenv\Dotenv;
use Luma\AuroraDatabase\DatabaseConnection;
use PHPUnit\Framework\TestCase;

class DatabaseConnectionTest extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__) . '/data');
        $dotenv->load();
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testItConnectsToDatabase(): void
    {
        $connection = $this->getTestClass();

        $this->assertInstanceOf(DatabaseConnection::class, $connection);
    }

    /**
     * @return void
     *
     * @throws \Exception
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
     *
     * @throws \Exception
     */
    private function getTestClass(): DatabaseConnection
    {
        return new DatabaseConnection(
            sprintf('mysql:host=%s;port=%d;', $_ENV['DB_HOST'], $_ENV['DB_PORT']),
            $_ENV['DB_USER'],
            $_ENV['DB_PASSWORD']
        );
    }
}