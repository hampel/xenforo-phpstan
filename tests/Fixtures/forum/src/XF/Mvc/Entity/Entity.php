<?php

namespace XF\Mvc\Entity;

abstract class Entity
{
    final public function save($throw = true, $newTransaction = true)
    {
        return true;
    }

    /**
     * @template T of Behavior
     *
     * @param class-string<T> $behavior
     *
     * @return T
     */
    public function getBehavior($behavior)
    {
        return new $behavior();
    }

    /**
     * @template T of Finder
     *
     * @param class-string<T> $identifier
     *
     * @return T
     */
    public function finder($identifier)
    {
        return \XF::em()->getFinder($identifier);
    }

    /**
     * @template T of Repository
     *
     * @param class-string<T> $identifier
     *
     * @return T
     */
    public function repository($identifier)
    {
        return \XF::em()->getRepository($identifier);
    }
}
