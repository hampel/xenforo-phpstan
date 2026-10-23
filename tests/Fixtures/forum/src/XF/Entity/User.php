<?php

namespace XF\Entity;

/**
 * @property-read UserOption|null $Option
 */
class User extends \XF\Mvc\Entity\Entity
{
    public function isMemberOf(int $groupId): bool
    {
        return false;
    }

    // Undeclared returns, as in XenForo: an extension's `return parent::...()` sees mixed.
    public function canFollowUser(User $user)
    {
        return false;
    }

    public function canViewFullProfile(&$error = null)
    {
        return true;
    }

    public function canViewBasicProfile(&$error = null)
    {
        return true;
    }

    public function hasAdminPermission($permissionId)
    {
        return false;
    }
}
