<?php

namespace XF\Service\User;

use XF\Service\AbstractService;
use XF\Service\ValidateAndSavableTrait;

// Renamed in 2.3; XF\Service\User\Registration is the runtime alias. _save() undeclared, as in XenForo.
class RegistrationService extends AbstractService
{
    use ValidateAndSavableTrait;

    protected $user;

    protected function _save()
    {
        return $this->user;
    }
}
