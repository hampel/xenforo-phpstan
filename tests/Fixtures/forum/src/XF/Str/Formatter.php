<?php

namespace XF\Str;

// As XenForo declares it: the link shape and the proxied URL undeclared.
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

    public function getProxiedUrlIfActive($type, $url)
    {
        return $this->getProxiedUrlIfActiveExtended($type, $url);
    }

    public function getProxiedUrlIfActiveExtended($type, $url, array $options = [])
    {
        if (!$this->proxyHandler) {
            return null;
        }

        $handler = $this->proxyHandler;

        return $handler($type, $url, $options);
    }

    /** @var callable|null */
    protected $proxyHandler;
}
