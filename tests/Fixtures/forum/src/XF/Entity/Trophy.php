<?php

namespace XF\Entity;

// As XenForo declares it: the master phrase undeclared.
class Trophy extends \XF\Mvc\Entity\Entity
{
    public function getMasterPhrase($title)
    {
        return new Phrase();
    }
}
