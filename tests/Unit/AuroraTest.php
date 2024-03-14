<?php

namespace Luma\Tests\Unit;

use Luma\DatabaseComponent\Aurora;
use Luma\DatabaseComponent\DatabaseConnection;
use Luma\Tests\Classes\Article;
use Luma\Tests\Classes\AuroraExtension;
use PHPUnit\Framework\TestCase;

class AuroraTest extends TestCase
{
    /**
     * @return void
     */
    public function testItSetsConnection(): void
    {
        $connection = new DatabaseConnection(
            'mysql:host=localhost;port=19909;',
            'root',
            'docker'
        );
        Aurora::setDatabaseConnection($connection);

        $this->assertEquals($connection, Aurora::getDatabaseConnection());

        $extensionClass = new AuroraExtension();

        $this->assertEquals($connection, $extensionClass::getDatabaseConnection());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testFind(): void
    {
        $connection = new DatabaseConnection(
            'mysql:host=localhost;port=19909;',
            'root',
            'docker'
        );
        Aurora::setDatabaseConnection($connection);

        $extensionClass = AuroraExtension::find(1);

        $this->assertEquals('Extension One', $extensionClass->getName());

        $article = Article::find(1);

        $this->assertEquals('Test Article', $article->getTitle());
        $this->assertEquals(1, $article->getUserId());

        $article = Article::find(7);

        $this->assertNull($article);
    }
}