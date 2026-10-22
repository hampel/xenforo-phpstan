<?php

namespace XF\Finder;

/**
 * @extends \XF\Mvc\Entity\Finder<\XF\Entity\User>
 */
class UserFinder extends \XF\Mvc\Entity\Finder
{
    public function isValidUser(): bool
    {
        return true;
    }
}
