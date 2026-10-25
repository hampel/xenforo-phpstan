<?php

namespace XF\Mvc\Entity;

class Manager
{
    public const INSTANTIATE_ALLOW_INVALID = 0x1;
    public const INSTANTIATE_PROXIED = 0x2;

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
     * @param array $entities
     *
     * @return ArrayCollection
     */
    public function getBasicCollection(array $entities)
    {
        return new ArrayCollection($entities);
    }

    /**
     * @return ArrayCollection
     */
    public function getEmptyCollection()
    {
        return new ArrayCollection([]);
    }

    /**
     * As XenForo documents it: nullable for an int-mask of the proxied flag, which includes 0,
     * the default.
     *
     * @template T of Entity
     *
     * @param class-string<T> $shortName
     * @param array $values
     * @param array $relations
     * @param int-mask-of<self::INSTANTIATE_*> $options
     *
     * @return ($options is int-mask<self::INSTANTIATE_PROXIED> ? T|null : T)
     */
    public function instantiateEntity($shortName, array $values = [], array $relations = [], $options = 0)
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
