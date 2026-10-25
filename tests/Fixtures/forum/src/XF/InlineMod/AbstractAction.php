<?php

namespace XF\InlineMod;

use XF\Mvc\Entity\Entity;

/**
 * @template T of Entity
 */
abstract class AbstractAction
{
    // As XenForo documents it; every core action returns a phrase.
    /**
     * @return string
     */
    abstract public function getTitle();
}
