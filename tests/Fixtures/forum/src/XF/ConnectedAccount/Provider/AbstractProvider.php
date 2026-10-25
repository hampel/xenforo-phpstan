<?php

namespace XF\ConnectedAccount\Provider;

abstract class AbstractProvider
{
    // As XenForo documents it; the body returns a phrase.
    /**
     * @return string
     */
    public function getTitle()
    {
        return '';
    }
}
