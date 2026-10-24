<?php

namespace XF\Util;

// As XenForo declares them: the paths undeclared, and getTempFile() returning tempnam() unchecked.
class File
{
    public static function getTempDir()
    {
        return sys_get_temp_dir();
    }

    public static function getTempFile($autoCleanUp = true)
    {
        return tempnam(static::getTempDir(), 'xf');
    }

    public static function getNamedTempFile($fileName, $autoCleanUp = true)
    {
        return static::getTempDir() . '/' . $fileName;
    }

    public static function createTempDir($autoCleanUp = true)
    {
        return static::getTempDir() . '/xf' . uniqid();
    }
}
