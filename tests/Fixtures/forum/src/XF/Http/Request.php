<?php

namespace XF\Http;

class Request
{
    public function filter($key, $type = null, $default = null)
    {
        return is_array($key) ? [] : $default;
    }

    // As XenForo declares it: a trimmed address on every path, undeclared.
    public function getIp($allowProxied = false)
    {
        return trim('127.0.0.1');
    }

    public function getUserAgent()
    {
        return $this->getServer('HTTP_USER_AGENT');
    }

    public function getBasePath()
    {
        return '/';
    }

    public function getFullBasePath()
    {
        return 'https://example.com' . $this->getBasePath();
    }

    public function getServer($key, $fallback = false)
    {
        return $fallback;
    }
}
