<?php

namespace XF\Repository;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;

class ActivityLogRepository extends Repository
{
    // As XenForo documents it: a list of maps, where its own handlers pass one map.
    /**
     * @param array<int, array<string, int>> $values
     */
    public function log(Entity $content, int $logDate, array $values, bool $increment = true): void
    {
    }
}
