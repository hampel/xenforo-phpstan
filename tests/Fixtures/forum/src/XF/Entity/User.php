<?php

namespace XF\Entity;

class User extends \XF\Mvc\Entity\Entity
{
    public function isMemberOf(int $groupId): bool
    {
        return false;
    }
}
