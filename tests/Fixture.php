<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Tests;

use Hampel\XenForo\PHPStan\Generator\Generator;
use Hampel\XenForo\PHPStan\Generator\Summary;

/**
 * The stand-in forum under tests/Fixtures/forum, generated into a fresh temporary directory.
 */
final class Fixture
{
    public static function forum(): string
    {
        return __DIR__ . '/Fixtures/forum';
    }

    public static function addon(): string
    {
        return self::forum() . '/src/addons/Acme/Demo';
    }

    public static function temp(string $name): string
    {
        $dir = sys_get_temp_dir() . '/xenforo-phpstan-' . $name . '-' . bin2hex(random_bytes(4));
        mkdir($dir, 0775, true);

        return $dir;
    }

    /** @return array{string, Summary} the directory generated into, and the summary */
    public static function generate(): array
    {
        $out = self::temp('generated');

        return [$out, (new Generator(self::forum()))->generate($out)];
    }

    public static function remove(string $dir): void
    {
        /** @var iterable<string, \SplFileInfo> $files */
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($dir);
    }
}
