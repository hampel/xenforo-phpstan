<?php

namespace XF\Template;

// As XenForo declares it: the markup undeclared, $escape set false by reference.
class Templater
{
    protected $defaultParams = [];

    public function addDefaultParam($name, $value)
    {
        $this->defaultParams[$name] = $value;
    }

    public function fnAvatar($templater, &$escape, $user, $size, $canonical = false, $attributes = [])
    {
        $escape = false;

        return '';
    }

    // As XenForo declares it: the result escaped unless told otherwise, and undeclared either way.
    public function func($name, array $arguments = [], $escape = true)
    {
        $value = $this->fnAvatar($this, $escape, ...$arguments);

        return $escape ? htmlspecialchars($value) : $value;
    }
}
