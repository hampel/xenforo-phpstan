<?php

namespace Acme\Other\XF\Entity;

// Registered with active="0": the class exists and XenForo never applies it, so its parent is
// declared and nothing it adds is reachable through the core type.
class UserInactive extends XFCP_UserInactive
{
    public function inactiveMethod(): string
    {
        return 'never applied';
    }
}
