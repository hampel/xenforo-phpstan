<?php

namespace Acme\Demo\XF\Entity;

class User extends XFCP_User
{
    public function canUseDemo(): bool
    {
        return $this->isMemberOf(3);
    }

    // An override with a wider signature: callable with two arguments only through this type.
    public function isMemberOf(int $groupId, bool $strict = false): bool
    {
        return parent::isMemberOf($groupId) && ! $strict;
    }
}
