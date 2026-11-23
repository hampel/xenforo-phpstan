<?php

namespace XF\Entity;

// As XenForo declares it: the master phrase undeclared.
class BbCode extends \XF\Mvc\Entity\Entity
{
    public function getMasterPhrase($type = 'title')
    {
        return new Phrase();
    }
}
