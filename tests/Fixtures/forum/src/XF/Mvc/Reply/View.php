<?php

namespace XF\Mvc\Reply;

class View extends AbstractReply
{
    /** @var array<string, mixed> */
    protected $params = [];

    public function getParams()
    {
        return $this->params;
    }

    public function getParam($key)
    {
        return $this->params[$key] ?? null;
    }
}
