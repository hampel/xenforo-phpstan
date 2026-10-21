<?php

namespace Acme\Demo\XF\Entity;

class User extends XFCP_User
{
    public function canUseDemo(): bool
    {
        return $this->isMemberOf(3);
    }
}
