<?php

namespace Luma\Tests\Unit;

use Dotenv\Dotenv;
use Luma\AuroraDatabase\DatabaseConnection;
use Luma\AuroraDatabase\Model\Aurora;
use Luma\AuroraDatabase\Utils\Collection;
use Luma\Tests\Classes\User;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
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
    }

    /**
     * @return void
     */
    public function testItCreatesCollection(): void
    {
        $collection = new Collection(['a', 1]);

        $this->assertInstanceOf(Collection::class, $collection);
    }

    /**
     * @return void
     */
    public function testGetIterator(): void
    {
        $collection = new Collection(['a', 1]);

        $this->assertInstanceOf(\ArrayIterator::class, $collection->getIterator());
    }

    /**
     * @return void
     */
    public function testGetAndSetMethods(): void
    {
        $collection = new Collection(['a', 1, 'hello', 'Test!']);

        $this->assertEquals(1, $collection->get(1));
        $this->assertEquals('a', $collection->first());
        $this->assertEquals('Test!', $collection->last());

        $collection = new Collection();

        $this->assertNull($collection->first());
        $this->assertNull($collection->last());
        $this->assertNull($collection->get(0));

        $collection->add(1);

        $this->assertEquals(1, $collection->first());
        $this->assertEquals(1, $collection->last());
        $this->assertEquals(1, $collection->get(0));

        $collection->remove(1);

        $this->assertNull($collection->first());
    }

    /**
     * @param array $data
     * @param callable $searchMethod
     * @param mixed $expected
     *
     * @return void
     *
     * @dataProvider collectionSearchDataProvider
     */
    public function testFind(array $data, callable $searchMethod, mixed $expected): void
    {
        $collection = new Collection($data);

        $this->assertEquals($expected, $collection->find($searchMethod));
    }

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $array = ['a' => 1,'b' => 2,'c' => 3];
        $collection = new Collection($array);

        $this->assertEquals($array, $collection->toArray());
    }

    /**
     * @return array[]
     */
    public static function collectionSearchDataProvider(): array
    {
        return [
            [
                'data' => [
                    1,
                    [
                        'hello' => 'world',
                    ],
                    'string',
                ],
                'searchMethod' => function ($item) {
                    return $item === 1;
                },
                'expected' => 1,
            ],
            [
                'data' => [
                    1,
                    [
                        'hello' => 'world',
                    ],
                    'string',
                ],
                'searchMethod' => function ($item) {
                    if (is_array($item) && array_key_exists('hello', $item)) {
                        return true;
                    }

                    return false;
                },
                'expected' => [
                    'hello' => 'world',
                ],
            ],
            [
                'data' => [
                    1,
                    [
                        'hello' => 'world',
                    ],
                    'string',
                ],
                'searchMethod' => function ($item) {
                    return $item === 7;
                },
                'expected' => null,
            ],
        ];
    }
}