<?php

namespace XF\Stats;

abstract class AbstractHandler
{
    // As XenForo documents it; the attachment handler returns round(..., 2), a float.
    /**
     * @param string $statsType
     * @param int $counter
     *
     * @return int
     */
    public function adjustStatValue($statsType, $counter)
    {
        return $counter;
    }
}
