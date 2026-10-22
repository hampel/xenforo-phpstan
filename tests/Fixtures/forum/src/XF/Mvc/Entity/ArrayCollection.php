<?php

namespace XF\Mvc\Entity;

/**
 * @template T of \XF\Mvc\Entity\Entity
 * @extends AbstractCollection<T>
 */
class ArrayCollection extends AbstractCollection
{
    public function count(): int
    {
        return 0;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator([]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return false;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }
}
