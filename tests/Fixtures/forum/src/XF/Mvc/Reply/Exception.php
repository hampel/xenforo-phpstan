<?php

namespace XF\Mvc\Reply;

class Exception extends \Exception
{
    public function __construct(AbstractReply $reply)
    {
        parent::__construct();
    }
}
