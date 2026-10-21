<?php

namespace XF\Service\User;

// Aliased to XF\Service\User\AbstractConfirmation: a declarable name for an abstract class.
abstract class AbstractConfirmationService extends \XF\Service\AbstractService
{
    abstract public function getType(): string;
}
