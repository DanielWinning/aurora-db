<?php

namespace Luma\Tests\Unit;

use Luma\DatabaseComponent\Model\Aurora;
use Luma\DatabaseComponent\DatabaseConnection;
use Luma\Tests\Classes\Article;
use Luma\Tests\Classes\AuroraExtension;
use Luma\Tests\Classes\InvalidAurora;
use Luma\Tests\Classes\User;
use PHPUnit\Framework\TestCase;

class AuroraTest extends TestCase
{
    protected DatabaseConnection $connection;

    protected function setUp(): void
    {
        $connection = new DatabaseConnection(
            'mysql:host=localhost;port=19909;',
            'root',
            'docker'
        );
        Aurora::setDatabaseConnection($connection);

        $this->connection = $connection;
    }

    /**
     * @return void
     */
    public function testItSetsConnection(): void
    {
        $this->assertEquals($this->connection, Aurora::getDatabaseConnection());

        $extensionClass = new AuroraExtension();

        $this->assertEquals($this->connection, $extensionClass::getDatabaseConnection());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testFind(): void
    {
        $extensionClass = AuroraExtension::find(1);

        $this->assertEquals('Extension One', $extensionClass->getName());

        $article = Article::find(1);

        $this->assertEquals('Test Article', $article->getTitle());
        $this->assertEquals(1, $article->getAuthor()->getId());

        $article = Article::find(7);

        $this->assertNull($article);
    }

    /**
     * @return User
     */
    public function testMake(): User
    {
        $user = User::make([
            'username' => 'Test User',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->getUsername());

        return $user;
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testGetPrimaryIdentifier(): void
    {
        $this->assertEquals('id', Article::getPrimaryIdentifierPropertyName());
        $this->assertEquals('intArticleId', Article::getPrimaryIdentifierColumnName());

        $this->expectException(\Exception::class);
        InvalidAurora::getPrimaryIdentifierPropertyName();
    }

    /**
     * @return void
     */
    public function testGetSchema(): void
    {
        $this->assertEquals('DatabaseComponentTest', AuroraExtension::getSchema());
        $this->assertEquals('DatabaseComponentTest', Article::getSchema());
    }

    /**
     * @return void
     */
    public function testGetTable(): void
    {
        $this->assertEquals('AuroraExtension', AuroraExtension::getTable());
        $this->assertEquals('Article', Article::getTable());
    }
}