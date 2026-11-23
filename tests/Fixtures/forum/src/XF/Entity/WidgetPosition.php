<?php

namespace XF\Entity;

// As XenForo declares it: both master phrases undeclared.
class WidgetPosition extends \XF\Mvc\Entity\Entity
{
    public function getMasterTitlePhrase()
    {
        return new Phrase();
    }

    public function getMasterDescriptionPhrase()
    {
        return new Phrase();
    }
}
