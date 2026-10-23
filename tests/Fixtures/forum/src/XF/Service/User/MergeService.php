<?php

namespace XF\Service\User;

use XF\Service\AbstractService;

// Renamed in 2.3; XF\Service\User\Merge is the runtime alias. Nothing declared, as in XenForo.
class MergeService extends AbstractService
{
    protected $source;

    protected $target;

    public function setSource(\XF\Entity\User $source)
    {
        $this->source = $source;

        return $this;
    }

    public function setTarget(\XF\Entity\User $target)
    {
        $this->target = $target;

        return $this;
    }

    public function merge($maxRunTime = 0)
    {
        return \XF\ContinuationResult::completed();
    }
}
