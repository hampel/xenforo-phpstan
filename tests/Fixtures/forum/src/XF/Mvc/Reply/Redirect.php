<?php

namespace XF\Mvc\Reply;

class Redirect extends AbstractReply
{
    /** @var string */
    protected $url = '';

    public function getUrl()
    {
        return $this->url;
    }
}
