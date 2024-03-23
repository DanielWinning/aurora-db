<?php

namespace Luma\AuroraDatabase\Utils;

class Collection implements \IteratorAggregate
{
    protected array $items;

    /**
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @return \Traversable
     */
    #[\Override]
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @param int $index
     *
     * @return mixed
     */
    public function get(int $index): mixed
    {
        return $this->items[$index] ?? null;
    }

    /**
     * @param mixed $item
     *
     * @return void
     */
    public function add(mixed $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @param mixed $item
     *
     * @return void
     */
    public function remove(mixed $item): void
    {
        $key = array_search($item, $this->items, true);

        if ($key !== false) {
            unset($this->items[$key]);
        }
    }

    /**
     * @return mixed
     */
    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    /**
     * @return mixed
     */
    public function last(): mixed
    {
        return count($this->items)
            ? end($this->items)
            : null;
    }
}