<?php

namespace Luma\Tests\Unit;

use Dotenv\Dotenv;
use Luma\DatabaseComponent\Model\Aurora;
use Luma\DatabaseComponent\DatabaseConnection;
use Luma\Tests\Classes\Article;
use Luma\Tests\Classes\AuroraExtension;
use Luma\Tests\Classes\InvalidAurora;
use Luma\Tests\Classes\User;
use PHPUnit\Framework\TestCase;

class AuroraTest extends TestCase
{
    private const INSERT_MESSAGE = 'Created by AuroraTest::testInsert';
    private const UPDATE_MESSAGE = 'Updated by AuroraTest::testUpdate';

    protected DatabaseConnection $connection;

    /**
     * @return void
     *
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__) . '/data');
        $dotenv->load();

        $connection = new DatabaseConnection(
            sprintf('mysql:host=%s;port=%d;', $_ENV['DB_HOST'], $_ENV['DB_PORT']),
            $_ENV['DB_USER'],
            $_ENV['DB_PASSWORD']
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

        $article = Article::find(99999999);

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
        $this->assertNull(InvalidAurora::getPrimaryIdentifierPropertyName());
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

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testInsert(): void
    {
        $article = Article::make([
            'title' => self::INSERT_MESSAGE,
            'author' => User::find(1),
        ])->save();

        $this->assertIsNumeric($article->getId());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testGetLatest(): void
    {
        $article = Article::getLatest();

        $this->assertInstanceOf(Article::class, $article);
        $this->assertIsNumeric($article->getId());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testUpdate(): void
    {
        $article = Article::getLatest();

        $this->assertEquals(self::INSERT_MESSAGE, $article->getTitle());

        $article->setTitle(self::UPDATE_MESSAGE);
        $article->save();

        // Get the article again to be certain we aren't just confirming the updated model
        $freshArticle = Article::find($article->getId());

        $this->assertEquals(self::UPDATE_MESSAGE, $freshArticle->getTitle());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testDelete(): void
    {
        $article = Article::getLatest();

        $this->assertInstanceOf(Article::class, $article);

        $articleId = $article->getId();

        $article->delete();

        $this->assertNotEquals($articleId, Article::getLatest());
    }

    /**
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testFindBy()
    {
        $user = User::findBy('username', 'Danny');

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('danny@test.com', $user->getEmailAddress());

        $this->expectException(\Exception::class);
        InvalidAurora::findBy('name', 'Test');
    }
}