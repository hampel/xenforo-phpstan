<?php

namespace XF;

// As XenForo declares it: the set for one add-on, undeclared.
class SimpleCache
{
    public function getSet($addOnId)
    {
        return new SimpleCacheSet($this, $addOnId);
    }
}
