<?php

namespace XF\ModeratorLog;

use XF\Entity\ModeratorLog;
use XF\Mvc\Entity\Entity;

/**
 * @template T of Entity
 */
abstract class AbstractHandler
{
    // As XenForo documents it; seventeen core handlers return a phrase.
    /**
     * @return string
     */
    public function getContentTitle(ModeratorLog $log)
    {
        return '';
    }
}
