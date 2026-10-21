<?php

// XenForo require()s this file for utf8_strlen() and friends; the fixture needs only its presence.
function utf8_strlen(string $string): int
{
    return mb_strlen($string);
}
