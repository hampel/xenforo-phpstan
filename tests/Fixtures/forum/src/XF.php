<?php

/**
 * A stand-in for XenForo's src/XF.php, with the four things the generator asks of the real
 * one: start(), $versionId, and the alias mapping in both directions. The real mapping is a
 * table of namespaces to suffixes; this carries the three suffixes the fixture uses and the
 * same rule - strip the suffix unless the stripped name is itself a class.
 */
class XF
{
    /** @var int */
    public static $versionId = 2031370; // abbccde = a.b.c d (alpha: 1, beta: 3, RC: 5, stable: 7, PL: 9) e

    public static ?XF\AutoLoader $autoLoader = null;

    private const SUFFIXES = ['Repository', 'Finder', 'Service', 'ControllerPlugin' => 'Plugin'];

    public static function start(string $root): void
    {
        require_once __DIR__ . '/XF/AutoLoader.php';

        self::$autoLoader = new XF\AutoLoader($root);
        spl_autoload_register([self::$autoLoader, 'load']);
    }

    public static function getAliasForClass(string $class): string
    {
        foreach (self::SUFFIXES as $namespace => $suffix) {
            $namespace = is_string($namespace) ? $namespace : $suffix;
            if (! str_contains($class, '\\' . $namespace . '\\') || ! str_ends_with($class, $suffix)) {
                continue;
            }

            $alias = substr($class, 0, -strlen($suffix));

            return self::$autoLoader?->findFile($alias) ? $class : $alias;
        }

        return $class;
    }

    public static function getClassForAlias(string $alias): string
    {
        foreach (self::SUFFIXES as $namespace => $suffix) {
            $namespace = is_string($namespace) ? $namespace : $suffix;
            if (! str_contains($alias, '\\' . $namespace . '\\') || str_ends_with($alias, $suffix)) {
                continue;
            }

            return self::$autoLoader?->findFile($alias . $suffix) ? $alias . $suffix : $alias;
        }

        return $alias;
    }

    public static function phrase($name, array $params = [], $allowHtml = true)
    {
        return new XF\Phrase();
    }

    public static function app(): XF\App
    {
        return new XF\App();
    }

    public static function em(): XF\Mvc\Entity\Manager
    {
        return self::app()->em();
    }

    /**
     * @template T of \XF\Mvc\Entity\Finder
     *
     * @param class-string<T> $identifier
     *
     * @return T
     */
    public static function finder($identifier)
    {
        return self::app()->finder($identifier);
    }

    /**
     * @template T of \XF\Mvc\Entity\Repository
     *
     * @param class-string<T> $identifier
     *
     * @return T
     */
    public static function repository($identifier)
    {
        return self::app()->repository($identifier);
    }

    /**
     * @template T of \XF\Service\AbstractService
     *
     * @param class-string<T> $class
     * @param mixed ...$args
     *
     * @return T
     */
    public static function service($class)
    {
        return self::app()->service(...func_get_args());
    }
}
