<?php

namespace Luma\Tests;

use Luma\DatabaseComponent\Aurora;
use Luma\DatabaseComponent\DatabaseConnection;
use PHPUnit\Framework\TestCase;

class AuroraTest extends TestCase
{
    public function testItSetsConnection(): void
    {
        $connection = new DatabaseConnection(
            'mysql:host=localhost;port=45009;',
            'root',
            'docker'
        );
        Aurora::setDatabaseConnection($connection);

        $this->assertEquals($connection, Aurora::getDatabaseConnection());

        $extensionClass = new AuroraExtension();

        $this->assertEquals($connection, $extensionClass::getDatabaseConnection());
    }
}