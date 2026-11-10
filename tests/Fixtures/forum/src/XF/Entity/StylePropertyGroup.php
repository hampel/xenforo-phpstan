<?php

namespace XF\Entity;

use XF\Phrase;

class StylePropertyGroup extends \XF\Mvc\Entity\Entity
{
    /**
     * @param bool $title
     *
     * @return Phrase|null
     */
    public function getMasterPhrase($title)
    {
        return null;
    }
}
