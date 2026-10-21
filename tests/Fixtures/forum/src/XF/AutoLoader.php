<?php

namespace XF;

/**
 * Stands in for the Composer class loader the real \XF::$autoLoader holds: findFile() is
 * what \XF::getAliasForClass() consults, and what the generator uses to read a class's file.
 */
class AutoLoader
{
    public function __construct(private string $root)
    {
    }

    public function findFile(string $class): string|false
    {
        $relative = str_replace('\\', '/', $class) . '.php';
        foreach (['/src/', '/src/addons/'] as $dir) {
            if (is_file($this->root . $dir . $relative)) {
                return $this->root . $dir . $relative;
            }
        }

        return false;
    }

    public function load(string $class): void
    {
        $file = $this->findFile($class);
        if ($file !== false) {
            require $file;
        }
    }
}
