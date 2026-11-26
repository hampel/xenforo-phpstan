<?php

namespace XF\Spam\Checker;

use XF\App;

// As XenForo declares it: the property bare, the accessor beside it documented.
abstract class AbstractProvider
{
    protected $app;

    /**
     * @return App
     */
    protected function app()
    {
        return $this->app;
    }
}
