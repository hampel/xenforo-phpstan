<?php

namespace Acme\Demo\XF\Repository;

// Registered on the legacy name XF\Repository\User, which the forum resolves to UserRepository.
class User extends XFCP_User
{
    public function findDemoUsers(): \XF\Finder\User
    {
        return $this->finder('XF:User');
    }
}
