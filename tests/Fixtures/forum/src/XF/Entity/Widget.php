<?php

namespace XF\Entity;

class Widget extends \XF\Mvc\Entity\Entity
{
    public function getMasterPhrase()
    {
        return new Phrase();
    }
}
