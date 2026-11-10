<?php

namespace XF\Entity;

abstract class AbstractPrefixGroup extends \XF\Mvc\Entity\Entity
{
    public function getMasterPhrase()
    {
        return new Phrase();
    }
}
