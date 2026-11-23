<?php

namespace XF\Mvc\Reply;

class View extends AbstractReply
{
    /** @var array<string, mixed> */
    protected $params = [];

    protected $templateName = '';

    // As XenForo declares the pair: whatever it was given, and so undeclared coming back.
    public function setTemplateName($string)
    {
        $this->templateName = $string;
    }

    public function getTemplateName()
    {
        return $this->templateName;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getParam($key)
    {
        return $this->params[$key] ?? null;
    }
}
