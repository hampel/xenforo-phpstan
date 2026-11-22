<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

use PHPStan\PhpDoc\TypeStringResolver;
use PHPStan\Type\Type;

/**
 * What an add-on has declared its container keys to hold, from `xenforo.container` and
 * `xenforo.subContainers` in its own configuration.
 *
 * A container entry is a closure registered at runtime, in an `app_setup` listener or a
 * sub-container's initialize(), so its type is a fact about the add-on that no docblock states
 * and the generator cannot read without running add-on code. The add-on states it once, as it
 * would in a `@var` beside every read, and each value is a PHPDoc type: a class name, or
 * anything else a `@return` could say. A key that is not declared has no type here, and the read
 * is left as PHPStan sees it.
 */
final class ContainerKeys
{
    /** @var array<string, Type> */
    private array $resolved = [];

    /**
     * @param  array<string, string>  $app  the application container's keys
     * @param  array<string, array<string, string>>  $subContainers  each sub-container class's own keys
     */
    public function __construct(
        private readonly array $app,
        private readonly array $subContainers,
        private readonly TypeStringResolver $types,
    ) {
    }

    /**
     * The type of a key of the application container.
     */
    public function inApp(string $key): ?Type
    {
        return isset($this->app[$key]) ? $this->type($this->app[$key]) : null;
    }

    /**
     * The type of a key of a sub-container's own container, looked up by the sub-container's
     * class and then each of its parents, nearest first.
     *
     * @param  list<string>  $lineage  the class and its ancestors
     */
    public function inSubContainer(array $lineage, string $key): ?Type
    {
        $declared = array_change_key_case($this->subContainers);

        foreach ($lineage as $class) {
            $keys = $declared[strtolower(ltrim($class, '\\'))] ?? [];
            if (isset($keys[$key])) {
                return $this->type($keys[$key]);
            }
        }

        return null;
    }

    private function type(string $declared): Type
    {
        return $this->resolved[$declared] ??= $this->types->resolve($declared);
    }
}
