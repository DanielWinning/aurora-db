<?php

namespace Luma\Tests\Unit;

use Luma\AuroraDatabase\Utils\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetIterator(): void
    {
        self::assertInstanceOf(\ArrayIterator::class, (new Collection(['a', 1]))->getIterator());
    }

    /**
     * @param Collection $collection
     * @param int $index
     * @param mixed $expected
     *
     * @return void
     */
    #[DataProvider('getDataProvider')]
    public function testGet(Collection $collection, int $index, mixed $expected): void
    {
        self::assertEquals($expected, $collection->get($index));
    }

    /**
     * @return void
     */
    public function testGetAndSetMethods(): void
    {
        $collection = new Collection(['a', 1, 'hello', 'Test!']);

        $this->assertEquals('a', $collection->first());
        $this->assertEquals('Test!', $collection->last());

        $collection = new Collection();

        $this->assertNull($collection->first());
        $this->assertNull($collection->last());

        $collection->add(1);

        $this->assertEquals(1, $collection->first());
        $this->assertEquals(1, $collection->last());

        $collection->remove(1);

        $this->assertNull($collection->first());
    }

    /**
     * @param array $data
     * @param callable $searchMethod
     * @param mixed $expected
     *
     * @return void
     */
    #[DataProvider('collectionSearchDataProvider')]
    public function testFind(array $data, callable $searchMethod, mixed $expected): void
    {
        self::assertEquals($expected, (new Collection($data))->find($searchMethod));
    }

    /**
     * @param array $data
     *
     * @return void
     */
    #[DataProvider('arrayProvider')]
    public function testToArray(array $data): void
    {
        self::assertEquals($data, (new Collection($data))->toArray());
    }

    /**
     * @param Collection $collection
     * @param bool $isEmpty
     *
     * @return void
     */
    #[DataProvider('isEmptyDataProvider')]
    public function testIsEmpty(Collection $collection, bool $isEmpty): void
    {
        self::assertEquals($isEmpty, $collection->isEmpty());
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

    /**
     * @return array<string, array<string, Collection|bool>>
     */
    public static function  isEmptyDataProvider(): array
    {
        return [
            'No argument provided' => [
                'collection' => new Collection(),
                'isEmpty' => true,
            ],
            'Empty array provided' => [
                'collection' => new Collection([]),
                'isEmpty' => true,
            ],
            'Populated array provided' => [
                'collection' => new Collection([1, 2, 3]),
                'isEmpty' => false,
            ],
        ];
    }

    /**
     * @return array[]
     */
    public static function getDataProvider(): array
    {
        return [
            'Get by index returns null when data is empty' => [
                'collection' => new Collection(),
                'index' => 0,
                'expected' => null,
            ],
            'Get by index returns correct index item' => [
                'collection' => new Collection([1, '2', 3]),
                'index' => 1,
                'expected' => '2',
            ],
        ];
    }

    /**
     * @return array[][]
     */
    public static function arrayProvider(): array
    {
        return [
            'Mixed types array' => [
                'data' => ['1', 0, false]
            ],
            'String array' => [
                'data' => ['hello', 'world']
            ],
            'Associative array' => [
                'data' => ['hello' => 'world']
            ],
        ];
    }
}