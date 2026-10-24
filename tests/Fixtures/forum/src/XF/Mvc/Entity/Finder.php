<?php

namespace XF\Mvc\Entity;

/**
 * @template T of \XF\Mvc\Entity\Entity
 * @implements \IteratorAggregate<string|int, T>
 */
class Finder implements \IteratorAggregate
{
    public function where($condition, $operator = null, $value = null)
    {
        return $this;
    }

    /**
     * @param $field
     * @param string $direction
     *
     * @return Finder
     */
    public function order($field, $direction = 'ASC')
    {
        return $this;
    }

    public function with($name, $mustExist = false)
    {
        return $this;
    }

    public function limit($limit, $offset = null)
    {
        return $this;
    }

    /**
     * @param int|null $offset
     *
     * @return null|T
     */
    public function fetchOne($offset = null)
    {
        return null;
    }

    /**
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return AbstractCollection<T>
     */
    public function fetch($limit = null, $offset = null)
    {
        return new ArrayCollection();
    }

    /**
     * @return AbstractCollection<T>
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return $this->fetch();
    }

    /**
     * @param $pluckFrom
     * @param null $keyedBy
     *
     * @return Finder
     */
    public function pluckFrom($pluckFrom, $keyedBy = null)
    {
        return $this;
    }
}
