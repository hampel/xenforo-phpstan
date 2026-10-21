<?php

namespace XF;

class App
{
    public function em(): Mvc\Entity\Manager
    {
        return new Mvc\Entity\Manager();
    }

    /**
     * @template T of Mvc\Entity\Entity
     *
     * @param class-string<T> $entity
     * @param mixed $id
     * @param array|string|null $with
     *
     * @return T|null
     */
    public function find($entity, $id, $with = [])
    {
        return $this->em()->find($entity, $id, $with);
    }

    /**
     * @template T of Mvc\Entity\Finder
     *
     * @param class-string<T> $identifier
     *
     * @return T
     */
    public function finder($identifier)
    {
        return $this->em()->getFinder($identifier);
    }

    /**
     * @template T of Mvc\Entity\Repository
     *
     * @param class-string<T> $identifier
     *
     * @return T
     */
    public function repository($identifier)
    {
        return $this->em()->getRepository($identifier);
    }

    /**
     * @template T of \XF\Service\AbstractService
     *
     * @param class-string<T> $class
     * @param mixed ...$arguments
     *
     * @return T
     */
    public function service($class, ...$arguments)
    {
        return new $class($this, ...$arguments);
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function data($class)
    {
        return new $class();
    }
}
