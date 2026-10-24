<?php

namespace XF\Template;

// As XenForo declares it: the markup undeclared, $escape set false by reference.
class Templater
{
    public function fnAvatar($templater, &$escape, $user, $size, $canonical = false, $attributes = [])
    {
        $escape = false;

        return '';
    }
}
