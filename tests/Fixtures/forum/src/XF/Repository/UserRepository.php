<?php

namespace XF\Repository;

// Renamed in 2.3; XF\Repository\User is the runtime alias.
class UserRepository extends \XF\Mvc\Entity\Repository
{
    public function getVisitor(): \XF\Entity\User
    {
        return new \XF\Entity\User();
    }
}
