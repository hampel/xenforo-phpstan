<?php

namespace XF\Mvc\Reply;

abstract class AbstractReply
{
    /** @var array<string, mixed> */
    protected $jsonParams = [];

    public function getJsonParams()
    {
        return $this->jsonParams;
    }
}
