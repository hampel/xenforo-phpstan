<?php

namespace XF;

class App
{
    /**
     * @return AddOn\Manager
     */
    public function addOnManager()
    {
        return new AddOn\Manager();
    }

    public function em(): Mvc\Entity\Manager
    {
        return new Mvc\Entity\Manager();
    }

    /**
     * @return Mvc\Router
     */
    public function router($type = null)
    {
        return new Mvc\Router();
    }

    /**
     * @return Db\AbstractAdapter
     */
    public function db()
    {
        return new Db\Mysqli\Adapter();
    }

    /**
     * @return Template\Templater
     */
    public function templater()
    {
        return new Template\Templater();
    }

    /**
     * @return Options
     */
    public function options()
    {
        return new Options();
    }

    /**
     * As XenForo documents it, which PHPStan reads as mixed on both paths.
     *
     * @param string|null $key
     *
     * @return Container|mixed
     */
    public function container($key = null)
    {
        return $key === null ? new Container() : null;
    }

    public function applyExternalDataUrl($externalPath, $canonical = false)
    {
        return $this->applyExternalDataUrlPathed($externalPath, $canonical ? 'canonical' : 'root-base');
    }

    public function applyExternalDataUrlPathed($externalPath, $pathType)
    {
        return '/data/' . $externalPath;
    }

    /**
     * @param string $contentType
     * @param int|array $contentId
     * @param string|array $with
     *
     * @return AbstractCollection|Mvc\Entity\Entity|null
     */
    public function findByContentType($contentType, $contentId, $with = [])
    {
        return null;
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
