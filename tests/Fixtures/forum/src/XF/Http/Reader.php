<?php

namespace XF\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * The docblocks are XenForo's, which document `$saveTo` and `$error` as null: the first is a path
 * or a stream to write the body to, and the second comes back holding the failure.
 */
class Reader
{
    /**
     * @param string $url
     * @param array $limits
     * @param null  $saveTo
     * @param array $options
     * @param null  $error
     *
     * @return ResponseInterface|null
     */
    public function get($url, array $limits = [], $saveTo = null, array $options = [], &$error = null)
    {
        return $this->request('get', $url, $limits, $saveTo, $options, $error);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $limits
     * @param null  $saveTo
     * @param array $options
     * @param null  $error
     *
     * @return ResponseInterface|null
     */
    public function request($method, $url, array $limits = [], $saveTo = null, array $options = [], &$error = null)
    {
        return null;
    }

    /**
     * @param string $url
     * @param array $limits
     * @param null  $saveTo
     * @param array $options
     * @param null  $error
     *
     * @return ResponseInterface|null
     */
    public function getUntrusted($url, array $limits = [], $saveTo = null, array $options = [], &$error = null)
    {
        return $this->requestUntrusted('get', $url, $limits, $saveTo, $options, $error);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $limits
     * @param null  $saveTo
     * @param array $options
     * @param null  $error
     *
     * @return ResponseInterface|null
     */
    public function requestUntrusted($method, $url, array $limits = [], $saveTo = null, array $options = [], &$error = null)
    {
        return null;
    }
}
