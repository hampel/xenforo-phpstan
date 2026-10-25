<?php

namespace XF\Str;

// As XenForo declares it: the link shape undeclared.
class Formatter
{
    public function getLinkClassTarget($url)
    {
        $internal = empty(parse_url($url, PHP_URL_HOST));

        return [
            'class' => $internal ? 'link link--internal' : 'link link--external',
            'target' => $internal ? '' : '_blank',
            'type' => $internal ? 'internal' : 'external',
            'trusted' => $internal,
            'local' => $internal,
        ];
    }
}
