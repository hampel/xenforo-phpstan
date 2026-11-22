<?php

namespace XF\SubContainer;

use XF\App;
use XF\Container;

abstract class AbstractSubContainer implements \ArrayAccess
{
    /**
     * @var Container
     */
    protected $parent;

    /**
     * @var App
     */
    protected $app;

    /**
     * @var Container
     */
    protected $container;

    abstract public function initialize();

    public function __construct(Container $parent, App $app)
    {
        $this->parent = $parent;
        $this->app = $app;

        $this->container = new Container();
        $this->initialize();
    }

    public function offsetExists($key): bool
    {
        return isset($this->container[$key]);
    }

    /**
     * @param mixed $key
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->container->offsetGet($key);
    }

    public function offsetSet($key, $value): void
    {
        $this->container->offsetSet($key, $value);
    }

    public function offsetUnset($key): void
    {
        $this->container->offsetUnset($key);
    }
}
