<?php

namespace XF\Mvc\Entity;

/**
 * @template T of \XF\Mvc\Entity\Entity
 * @implements \IteratorAggregate<string|int, T>
 * @implements \ArrayAccess<string|int, T>
 */
abstract class AbstractCollection implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @return \ArrayIterator<string|int, T>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator([]);
    }

    public function keys()
    {
        return [];
    }

    /**
     * @return array<int|string,T>
     */
    public function toArray()
    {
        return [];
    }

    // As XenForo documents it; the body is array_filter(), which keeps by truthiness.
    /**
     * @param callable(T $entity): bool $callback
     *
     * @return AbstractCollection<T>
     */
    public function filter(\Closure $callback)
    {
        return $this;
    }

    // As XenForo documents it: the callable's return parses as `int`, with `string` a sibling.
    /**
     * @param int|string|callable(T $e): int|string $grouper
     * @param int|string|callable(T $e): int|string $childKeyFn
     * @return array<int|string, array<int|string,T>>
     */
    public function groupBy($grouper, $childKeyFn = null)
    {
        return [];
    }
}
