<?php

namespace Acme\Demo\Service\Widget;

// The add-on's own renamed class; Acme\Demo\Service\Widget\Create is its runtime alias.
class CreateService extends \XF\Service\AbstractService
{
    use \XF\Service\ValidateAndSavableTrait;

    public function setName(string $name): static
    {
        return $this;
    }

    protected function _save(): bool
    {
        return true;
    }
}
