<?php

namespace XF\Finder;

class UserFinder extends \XF\Mvc\Entity\Finder
{
    public function isValidUser(): bool
    {
        return true;
    }
}
