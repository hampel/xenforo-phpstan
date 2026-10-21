<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Reflection;

use Hampel\XenForo\PHPStan\Classes;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;

/**
 * A XenForo class extension adds methods to a core class at runtime: Vendor\AddOn\XF\Entity\User
 * extends XFCP_User, XenForo splices it under the real XF\Entity\User, and every $user in the
 * forum can call what it added. Statically every call site is typed to the core class, so PHPStan
 * reports each of those methods as undefined. This answers for them from the extension classes
 * `xenforo-phpstan generate` recorded, for the core class and anything below it.
 *
 * Only a method the extension class itself declares is offered - one the core class already has
 * never reaches here, and one inherited from the core class through XFCP_ must not be offered back
 * to it, which is also what keeps the lookup from recursing.
 */
final class ClassExtensionMethodsExtension implements MethodsClassReflectionExtension
{
    /** @var array<string, true> */
    private array $resolving = [];

    public function __construct(
        private readonly Classes $classes,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        return $this->find($classReflection, $methodName) !== null;
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        $method = $this->find($classReflection, $methodName);
        if ($method === null) {
            throw new \LogicException(sprintf('%s::%s() was not offered by this extension.', $classReflection->getName(), $methodName));
        }

        return $method;
    }

    private function find(ClassReflection $class, string $methodName): ?MethodReflection
    {
        $lineage = [$class->getName(), ...$class->getParentClassesNames()];

        foreach ($lineage as $ancestor) {
            foreach ($this->classes->extensionsOf($ancestor) as $extension) {
                if (in_array($extension, $lineage, true) || ! $this->reflectionProvider->hasClass($extension)) {
                    continue;
                }

                $key = $extension . '::' . $methodName;
                if (isset($this->resolving[$key])) {
                    continue;
                }

                $this->resolving[$key] = true;

                try {
                    $reflection = $this->reflectionProvider->getClass($extension);
                    if (! $reflection->hasNativeMethod($methodName)) {
                        continue;
                    }

                    $method = $reflection->getNativeMethod($methodName);
                    if (in_array($method->getDeclaringClass()->getName(), $lineage, true)) {
                        continue;
                    }

                    return $method;
                } finally {
                    unset($this->resolving[$key]);
                }
            }
        }

        return null;
    }
}
