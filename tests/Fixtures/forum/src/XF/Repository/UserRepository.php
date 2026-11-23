<?php

namespace XF\Repository;

// Renamed in 2.3; XF\Repository\User is the runtime alias.
class UserRepository extends \XF\Mvc\Entity\Repository
{
    public function getVisitor(): \XF\Entity\User
    {
        return new \XF\Entity\User();
    }

    /**
     * XenForo's docblock, which names no entity for the collection.
     *
     * @param array $usernames
     * @param array $notFound
     * @param array $with
     * @param bool $validOnly
     * @param array $extraWhere
     *
     * @return \XF\Mvc\Entity\ArrayCollection
     */
    public function getUsersByNames(array $usernames, &$notFound = [], $with = [], $validOnly = false, $extraWhere = [])
    {
        $notFound = [];

        return new \XF\Mvc\Entity\ArrayCollection([]);
    }
}
