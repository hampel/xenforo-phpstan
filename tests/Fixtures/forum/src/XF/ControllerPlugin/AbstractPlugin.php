<?php

namespace XF\ControllerPlugin;

abstract class AbstractPlugin
{
    public function __construct(protected \XF\Mvc\Controller $controller)
    {
    }
}
