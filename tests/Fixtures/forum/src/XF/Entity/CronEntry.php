<?php

namespace XF\Entity;

// As XenForo declares it: the master phrase undeclared.
class CronEntry extends \XF\Mvc\Entity\Entity
{
    public function getMasterPhrase()
    {
        return new Phrase();
    }
}
