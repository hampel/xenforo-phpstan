<?php

namespace Acme\Demo\XF\Entity;

/**
 * A column and a relation the extension adds in getStructure(), declared as XenForo declares
 * its own and as xf-dev:entity-class-properties writes them.
 *
 * @property string $acme_demo_note
 * @property-read \XF\Entity\UserOption|null $AcmeOption
 */
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
