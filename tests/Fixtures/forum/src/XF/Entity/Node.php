<?php

namespace XF\Entity;

class Node extends \XF\Mvc\Entity\Entity
{
    public function getBreadcrumbs($includeSelf = true, $linkType = 'public')
    {
        return [];
    }
}
