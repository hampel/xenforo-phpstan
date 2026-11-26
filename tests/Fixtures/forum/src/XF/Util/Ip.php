<?php

namespace XF\Util;

class Ip
{
    /**
     * As XenForo documents it, where no path returns true.
     *
     * @param string|int $ip
     *
     * @return bool|string False on failure, binary data otherwise
     */
    public static function convertIpStringToBinary($ip)
    {
        return inet_pton((string) $ip);
    }

    /**
     * @param string $ip Binary IP data
     * @param bool $shorten
     *
     * @return bool|string
     */
    public static function convertIpBinaryToString($ip, $shorten = true)
    {
        return inet_ntop($ip);
    }
}
