<?php

namespace XF\Api\Controller;

use XF\Api\Mvc\Reply\ApiResult;
use XF\Api\Result\ArrayResult;
use XF\Entity\ResultInterface;
use XF\Mvc\Controller;
use XF\Mvc\Reply\Error;

// As XenForo declares them: four helpers every API action returns through, none with a type.
abstract class AbstractController extends Controller
{
    public function apiError($errorMessage, $errorCode, ?array $params = null, $httpCode = 400)
    {
        return new Error();
    }

    public function apiResult($result)
    {
        if (is_array($result)) {
            $result = new ArrayResult($result);
        } elseif (! ($result instanceof ResultInterface)) {
            throw new \LogicException('Must pass a result or an array');
        }

        return new ApiResult($result);
    }

    public function apiSuccess(array $extra = [])
    {
        return $this->apiBoolResult(true, $extra);
    }

    public function apiBoolResult(bool $success, array $extra = [])
    {
        return $this->apiResult(['success' => $success] + $extra);
    }
}
