<?php

namespace XF\Repository;

// Renamed in 2.3; XF\Repository\Tag is the runtime alias. As XenForo declares it: no return type.
class TagRepository extends \XF\Mvc\Entity\Repository
{
    public function normalizeTag($tag)
    {
        $tag = preg_replace('/\s+/u', ' ', strtolower($tag));

        return trim($tag);
    }
}
