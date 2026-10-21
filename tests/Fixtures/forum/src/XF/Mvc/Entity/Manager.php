<?php

namespace XF\Mvc\Entity;

class Manager
{
    /**
     * @template T of Entity
     *
     * @param class-string<T> $shortName
     *
     * @return T
     */
    public function create($shortName)
    {
        return new $shortName();
    }

    /**
     * @template T of Entity
     *
     * @param class-string<T> $shortName
     * @param mixed $id
     * @param array|string|null $with
     *
     * @return T|null
     */
    public function find($shortName, $id, $with = null)
    {
        return null;
    }

    /**
     * @template T of Entity
     *
     * @param class-string<T> $shortName
     * @param array $where
     * @param array|string|null $with
     *
     * @return T|null
     */
    public function findOne($shortName, array $where, $with = null)
    {
        return null;
    }

    /**
     * @template T of Finder
     *
     * @param class-string<T> $shortName
     * @param bool $includeDefaultWith
     *
     * @return T
     */
    public function getFinder($shortName, $includeDefaultWith = true)
    {
        return new $shortName();
    }

    /**
     * @template T of Repository
     *
     * @param class-string<T> $identifier
     *
     * @return T
     */
    public function getRepository($identifier)
    {
        return new $identifier();
    }
}
