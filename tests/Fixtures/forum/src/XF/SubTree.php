<?php

namespace XF;

class SubTree implements \ArrayAccess, \IteratorAggregate, \Countable
{
    public $id;

    public $record;

    public function __construct($id, $record, Tree $containingTree)
    {
        $this->id = $id;
        $this->record = $record;
    }

    public function children()
    {
        return [];
    }

    public function parent()
    {
        return null;
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
