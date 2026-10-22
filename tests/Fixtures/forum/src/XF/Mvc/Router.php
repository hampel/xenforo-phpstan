<?php

namespace XF\Mvc;

class Router
{
    public function buildLink($link, $data = null, array $parameters = [], $hash = null)
    {
        return (string) $link;
    }
}
