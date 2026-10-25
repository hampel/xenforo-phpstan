<?php

namespace XF\Repository;

use XF\Mvc\Entity\Repository;

class AttachmentRepository extends Repository
{
    // As XenForo documents it: PHPStan reads the union as a collection only; the body iterates.
    /**
     * @param \XF\Mvc\Entity\AbstractCollection|\XF\Mvc\Entity\Entity[] $content
     * @param $contentType
     * @param string $countKey
     * @param string $relationKey
     *
     * @return mixed
     */
    public function addAttachmentsToContent($content, $contentType, $countKey = 'attach_count', $relationKey = 'Attachments')
    {
        return null;
    }
}
