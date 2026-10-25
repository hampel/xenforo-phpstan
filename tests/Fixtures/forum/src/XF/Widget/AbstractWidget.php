<?php

namespace XF\Widget;

abstract class AbstractWidget
{
    public function __construct(protected \XF\App $app)
    {
    }

    // As XenForo documents it; every core widget returns $this->renderer().
    /**
     * @return string|null
     */
    abstract public function render();

    /**
     * @param array<string, mixed> $viewParams
     *
     * @return WidgetRenderer
     */
    public function renderer($templateName = '', array $viewParams = [])
    {
        return new WidgetRenderer();
    }

    /**
     * @template T of \XF\Mvc\Entity\Repository
     *
     * @param class-string<T> $repository
     *
     * @return T
     */
    public function repository($repository)
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
    public function finder($finder)
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
    public function findOne($finder, array $where, $with = null)
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
