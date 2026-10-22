<?php

namespace XF\Http;

class Request
{
    public function filter($key, $type = null, $default = null)
    {
        return is_array($key) ? [] : $default;
    }
}
