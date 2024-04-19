<?php

namespace Luma\Tests\Unit;

use Dotenv\Dotenv;
use Luma\AuroraDatabase\Model\Aurora;
use Luma\AuroraDatabase\DatabaseConnection;
use Luma\AuroraDatabase\Utils\Collection;
use Luma\Tests\Classes\AddressDetails;
use Luma\Tests\Classes\Article;
use Luma\Tests\Classes\AuroraExtension;
use Luma\Tests\Classes\InvalidAurora;
use Luma\Tests\Classes\Permission;
use Luma\Tests\Classes\Role;
use Luma\Tests\Classes\User;
use PHPUnit\Framework\TestCase;
use Tracy\Debugger;

class AuroraTest extends TestCase
{
    private const string INSERT_MESSAGE = 'Created by AuroraTest::testInsert';
    private const string UPDATE_MESSAGE = 'Updated by AuroraTest::testUpdate';

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
            sprintf('mysql:host=%s;port=%d;', $_ENV['DATABASE_HOST'], $_ENV['DATABASE_PORT']),
            $_ENV['DATABASE_USER'],
            $_ENV['DATABASE_PASSWORD']
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

        $addressDetails = AddressDetails::find(1);

        $this->assertEquals('1 Main Street', $addressDetails->getAddressLineOne());
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $user = User::create([
            'username' => 'Test User',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->getUsername());

        $articleOne = Article::create([
            'title' => 'Unit Test Article One',
            'author' => $user,
            'created' => new \DateTime(),
        ]);

        $this->assertInstanceOf(Article::class, $articleOne);
        $this->assertEquals('Unit Test Article One', $articleOne->getTitle());
        $this->assertEquals($user->getUsername(), $articleOne->getAuthor()->getUsername());

        $editArticlePermission = Permission::create([
            'name' => 'Edit Article',
            'handle' => 'edit_article',
        ]);

        $this->assertInstanceOf(Permission::class, $editArticlePermission);
        $this->assertEquals('Edit Article', $editArticlePermission->getName());

        $adminRole = Role::create([
            'name' => 'Admin',
            'handle' => 'admin',
            'permissions' => new Collection([$editArticlePermission]),
        ]);

        $this->assertInstanceOf(Role::class, $adminRole);
        $this->assertInstanceOf(Collection::class, $adminRole->getPermissions());
        $this->assertEquals($editArticlePermission->getName(), $adminRole->getPermissions()->first()->getName());
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
        $article = Article::create([
            'title' => self::INSERT_MESSAGE,
            'author' => User::find(1),
        ])->save();

        $this->assertIsNumeric($article->getId());

        $addressDetails = AddressDetails::create([
            'addressLineOne' => '2 Main Street',
            'city' => 'London',
            'postcode' => 'E1 1AB',
            'user' => User::find(1),
        ])->save();

        $this->assertIsNumeric($addressDetails->getId());

        $editArticlePermission = Permission::create([
            'name' => 'Edit Article',
            'handle' => 'edit_article',
        ])->save();

        $this->assertIsNumeric($editArticlePermission->getId());

        $adminRole = Role::create([
            'name' => 'Admin',
            'handle' => 'admin',
            'permissions' => new Collection([$editArticlePermission]),
        ])->save();

        $this->assertIsNumeric($adminRole->getId());

        $adminRoleRetrieved = Role::getLatest();

        $this->assertInstanceOf(Role::class, $adminRoleRetrieved);
        $this->assertEquals('admin', $adminRoleRetrieved->getHandle());

        $adminRole->delete();
        $editArticlePermission->delete();
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

        //$this->assertEquals(self::INSERT_MESSAGE, $article->getTitle());

        $article->setTitle(self::UPDATE_MESSAGE);
        $article->save();

        // Get the article again to be certain we aren't just confirming the updated model
        $freshArticle = Article::find($article->getId());

        $this->assertEquals(self::UPDATE_MESSAGE, $freshArticle->getTitle());

        $user = User::create([
            'username' => 'Test User',
            'strEmailAddress' => 'test_user@test.com',
            'password' => 'password',
            'roles' => new Collection([
                Role::create([
                    'name' => 'Guest',
                    'handle' => 'guest',
                ]),
            ]),
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Collection::class, $user->getRoles());

        $user->save();

        $user = User::getLatest();

        $this->assertInstanceOf(User::class, $user);

        $user->with([Role::class]);

        $this->assertEquals('Guest', $user->getRoles()->get(0)->getName());

        $superUserRole = Role::create([
            'name' => 'Super User',
            'handle' => 'super_user',
        ]);

        $user->getRoles()->add($superUserRole);
        $user->save();

        $guestRole = Role::findBy('handle', 'guest');
        $superUserRole = Role::getLatest();
        $guestRole->delete();
        $superUserRole->delete();
        $user->delete();
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

    /**
     * @return void
     */
    public function testAll(): void
    {
        $articles = Article::all();

        $this->assertNotEmpty($articles);
        $this->assertEquals(1, $articles[0]->getId());
        $this->assertEquals('Danny', $articles[0]->getAuthor()->getUsername());
    }

    /**
     * @return void
     */
    public function testCount(): void
    {
        $this->assertEquals(1, User::count());
        $this->assertEquals(3, AuroraExtension::count());
    }

    public function testPaginate(): void
    {
        $articles = Article::paginate();

        $this->assertInstanceOf(Collection::class, $articles);
        $this->assertNotEmpty($articles);
        $this->assertCount(10, $articles);

        $articles = Article::paginate(2, 5);

        $this->assertCount(5, $articles);
        $this->assertEquals(6, $articles->get(0)->getId());

        $articles = Article::paginate(1, 10, 'id', 'DESC');

        $this->assertNotEquals(1, $articles->get(0)->getId());

        $articles = Article::paginate(1, 10, 'content');

        $this->assertInstanceOf(Article::class, $articles->first());

        $users = User::paginate();

        $this->assertInstanceOf(Collection::class, $users);
        $this->assertInstanceOf(User::class, $users->first());
    }

    /**
     * @return void
     */
    public function testQueryBuilderSelect(): void
    {
        $user = User::select()->get();

        // As there is only one record in the User table, just return that record as a mapped Aurora model
        $this->assertInstanceOf(User::class, $user);

        $articles = Article::select()->get();

        $this->assertIsArray($articles);
        $this->assertInstanceOf(Article::class, $articles[0]);

        $user = User::select(['username'])->get();

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('Danny', $user->getUsername());

        $this->expectException(\Error::class);
        $emailAddress = $user->getEmailAddress();
    }

    /**
     * @return void
     */
    public function testWhereIs(): void
    {
        $extension = AuroraExtension::select()->whereIs('name', 'Extension Two')->get();

        $this->assertInstanceOf(AuroraExtension::class, $extension);
        $this->assertEquals(2, $extension->getId());

        $extension = AuroraExtension::select()->whereIs('name', 'Extension One')->whereIs('id', 2)->get();

        $this->assertNull($extension);

        $articles = Article::select()
            ->whereIs('title', 'Test Article')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get();

        $this->assertIsArray($articles);
        $this->assertCount(5, $articles);
        $this->assertGreaterThan($articles[1]->getId(), $articles[0]->getId());
    }

    /**
     * @return void
     */
    public function testWhereIn(): void
    {
        $extensions = AuroraExtension::select()
            ->whereIn('name', ['Extension Two', 'Extension Three'])
            ->get();

        $this->assertCount(2, $extensions);
        $this->assertEquals(2, $extensions[0]->getId());

        $extensions = AuroraExtension::select()->whereIn('id', [1, 3])->get();

        $this->assertCount(2, $extensions);
        $this->assertEquals('Extension Three', $extensions[1]->getName());
    }

    /**
     * @return void
     */
    public function testWhereNot(): void
    {
        $extensions = AuroraExtension::select()->whereNot('id', 2)->get();

        $this->assertIsArray($extensions);
        $this->assertCount(2, $extensions);
        $this->assertEquals(1, $extensions[0]->getId());
        $this->assertEquals(3, $extensions[1]->getId());
    }

    /**
     * @return void
     */
    public function testWhereNotIn(): void
    {
        $extension = AuroraExtension::select()->whereNotIn('id', [1, 3])->get();

        $this->assertInstanceOf(AuroraExtension::class, $extension);
        $this->assertEquals(2, $extension->getId());
    }

    /**
     * @return void
     */
    public function testWith()
    {
        $user = User::find(1);

        $this->assertInstanceOf(Aurora::class, $user);

        $articlesOne = $user->with([Article::class])->getArticles();

        $this->assertInstanceOf(Collection::class, $articlesOne);
        $this->assertInstanceOf(Article::class, $articlesOne->first());

        $articlesTwo = $user->with([Article::class])->getArticles();

        $this->assertEquals($articlesOne->count(), $articlesTwo->count());

        $permission = Permission::create([
            'name' => 'Edit',
            'handle' => 'edit',
        ]);
        $role = Role::create([
            'name' => 'Administrator',
            'handle' => 'admin',
            'permissions' => new Collection([$permission]),
        ])->save();

        $this->assertIsNumeric($permission->getId());

        $roleFromDatabase = Role::select()->whereIs('handle', 'admin')
            ->get()
            ->with([Permission::class]);
        $this->assertInstanceOf(Collection::class, $roleFromDatabase->getPermissions());

        $newRole = Role::create([
            'name' => 'Default',
            'handle' => 'default',
            'permissions' => new Collection([1]),
        ])->save();

        $newRole->delete();
        $role->delete();
        $permission->delete();
    }

    /**
     * @return void
     */
    public function testDebuggerEnabled(): void
    {
        Debugger::enable();
        Aurora::createQueryPanel();

        User::find(1);

        $this->expectNotToPerformAssertions();
    }
}