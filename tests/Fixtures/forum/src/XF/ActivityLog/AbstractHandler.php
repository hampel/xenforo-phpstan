<?php

namespace XF\ActivityLog;

use XF\Mvc\Entity\Entity;

/**
 * @template T of Entity
 */
abstract class AbstractHandler
{
    public function __construct(protected string $contentType)
    {
    }

    // As XenForo documents them: the parameters are positional (the body appends one), and the
    // thread handler passes the first post where T is the thread.
    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, string|int>
     */
    protected function getReplyMetricsSimple(string $table, string $dateColumn, string $where, array $params): array
    {
        return [];
    }

    /**
     * @param T $content
     *
     * @return array<string, string|int>
     */
    protected function getReactionMetrics(Entity $content): array
    {
        return [];
    }
}
