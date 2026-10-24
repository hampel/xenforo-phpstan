<?php

namespace XF\Http;

class Request
{
    public function filter($key, $type = null, $default = null)
    {
        return is_array($key) ? [] : $default;
    }

    public function getUserAgent()
    {
        return $this->getServer('HTTP_USER_AGENT');
    }

    public function getServer($key, $fallback = false)
    {
        return $fallback;
    }
}
