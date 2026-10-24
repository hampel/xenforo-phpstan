<?php

namespace Acme\Demo;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepResult;

// XenForo documents the three step methods as returning null|StepResult. A setup with one step
// returns nothing, as most do; without the stub that is a missing return in every such add-on.
class Setup extends AbstractSetup
{
    public function install(array $stepParams = [])
    {
        $this->createTables();
    }

    public function upgrade(array $stepParams = [])
    {
        return new StepResult();
    }

    public function uninstall(array $stepParams = [])
    {
        return null;
    }

    private function createTables(): void
    {
    }
}
