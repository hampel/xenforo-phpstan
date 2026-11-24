<?php

namespace XF\Api\Mvc\Reply;

use XF\Entity\ResultInterface;
use XF\Mvc\Reply\AbstractReply;

// As XenForo declares it: the result typed going in and undeclared coming back.
class ApiResult extends AbstractReply
{
    /**
     * @var ResultInterface
     */
    protected $apiResult;

    public function __construct(ResultInterface $result)
    {
        $this->setApiResult($result);
    }

    public function setApiResult(ResultInterface $result)
    {
        $this->apiResult = $result;
    }

    public function getApiResult()
    {
        return $this->apiResult;
    }
}
