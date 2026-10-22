<?php

namespace XF;

class Tree implements \ArrayAccess, \IteratorAggregate, \Countable
{
    protected $data;

    public function __construct($data, $parentIdKey = 'parent_id', $root = 0)
    {
        $this->data = $data;
    }

    public function children($id = null)
    {
        return [];
    }

    public function getData($id)
    {
        return null;
    }

    public function parent($id)
    {
        return null;
    }

    public function getFlattened($depth = 0, $rootId = null)
    {
        return [];
    }

    public function getDescendants($rootId = null)
    {
        return [];
    }

    /**
     * @return Tree
     */
    public function filter(?\Closure $preOrder = null, ?\Closure $postOrder = null)
    {
        return new self([]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return null;
    }

    public function offsetSet($offset, $value): void
    {
    }

    public function offsetExists($offset): bool
    {
        return false;
    }

    public function offsetUnset($offset): void
    {
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->children());
    }

    public function count(): int
    {
        return 0;
    }
}
