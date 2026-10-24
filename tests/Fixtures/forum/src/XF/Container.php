<?php

namespace XF;

class Container implements \ArrayAccess
{
    public function offsetExists($key): bool
    {
        return false;
    }

    public function offsetGet($key): mixed
    {
        return null;
    }

    public function offsetSet($key, $value): void
    {
    }

    public function offsetUnset($key): void
    {
    }

    public function set($key, $value)
    {
    }
}
