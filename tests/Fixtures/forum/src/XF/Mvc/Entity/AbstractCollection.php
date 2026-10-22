<?php

namespace XF\Mvc\Entity;

/**
 * @template T of \XF\Mvc\Entity\Entity
 * @implements \IteratorAggregate<string|int, T>
 * @implements \ArrayAccess<string|int, T>
 */
abstract class AbstractCollection implements \Countable, \IteratorAggregate, \ArrayAccess
{
    public function keys()
    {
        return [];
    }
}
