<?php

namespace Acme\Demo\ActivityLog;

use XF\ActivityLog\AbstractHandler;
use XF\Entity\User;
use XF\Mvc\Entity\Entity;

/**
 * A container handler, as XenForo's thread handler is: its reaction metrics are a child's.
 * XenForo documents getReactionMetrics() as taking T, so without the stub's correction the child
 * is reported against the handler's own entity.
 *
 * @extends AbstractHandler<User>
 */
class UserHandler extends AbstractHandler
{
    /** @return array<string, string|int> */
    public function childMetrics(Entity $child): array
    {
        return $this->getReactionMetrics($child);
    }
}
