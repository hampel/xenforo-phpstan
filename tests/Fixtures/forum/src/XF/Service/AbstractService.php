<?php

namespace XF\Service;

// Aliased to XF\Service\Abstract at runtime - a reserved word, so the stub must skip it.
abstract class AbstractService
{
    public function __construct(protected \XF\App $app)
    {
    }

    /**
     * @template T of \XF\Mvc\Entity\Repository
     *
     * @param class-string<T> $repository
     *
     * @return T
     */
    protected function repository($repository)
    {
        return $this->app->repository($repository);
    }

    /**
     * @template T of \XF\Mvc\Entity\Finder
     *
     * @param class-string<T> $finder
     *
     * @return T
     */
    protected function finder($finder)
    {
        return $this->app->finder($finder);
    }

    /**
     * @template T of \XF\Mvc\Entity\Entity
     *
     * @param class-string<T> $finder
     * @param array $where
     * @param array|string|null $with
     *
     * @return T|null
     */
    protected function findOne($finder, array $where, $with = null)
    {
        return $this->app->em()->findOne($finder, $where, $with);
    }

    /**
     * @template T of \XF\Service\AbstractService
     *
     * @param class-string<T> $class
     * @param mixed ...$arguments
     *
     * @return T
     */
    public function service($class)
    {
        return $this->app->service(...func_get_args());
    }
}
