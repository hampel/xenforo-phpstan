<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Reflection;

use Hampel\XenForo\PHPStan\Classes;
use PHPStan\PhpDoc\Tag\PropertyTag;
use PHPStan\Reflection\Annotations\AnnotationPropertyReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;

/**
 * The property counterpart of ClassExtensionMethodsExtension. A class extension that adds a column
 * or a relation to a core entity in getStructure() declares it the way XenForo declares its own,
 * as `@property` or `@property-read` on the extension class - xf-dev:entity-class-properties
 * writes those lines - and every $user in the forum then has it. Statically a value typed to the
 * core class does not, and `$visitor->vendor_addon_column` is reported from level 2. This offers
 * every property the recorded extensions of the core class, and of anything below it, declare.
 *
 * A property the receiver's own lineage declares is left to PHPStan: this extension is consulted
 * before PHPStan's own reading of `@property` lines, so an extension that restates a core column
 * must not answer ahead of the core class's declaration of it.
 */
final class ClassExtensionPropertiesExtension implements PropertiesClassReflectionExtension
{
    /** @var array<string, true> */
    private array $resolving = [];

    public function __construct(
        private readonly Classes $classes,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return $this->find($classReflection, $propertyName) !== null;
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        $found = $this->find($classReflection, $propertyName);
        if ($found === null) {
            throw new \LogicException(sprintf('%s::$%s was not offered by this extension.', $classReflection->getName(), $propertyName));
        }

        [$declaringClass, $tag] = $found;
        $mixed = new MixedType();

        return new AnnotationPropertyReflection(
            $propertyName,
            $declaringClass,
            $tag->getReadableType() ?? $mixed,
            $tag->getWritableType() ?? $mixed,
            $tag->isReadable(),
            $tag->isWritable(),
        );
    }

    /** @return array{ClassReflection, PropertyTag}|null */
    private function find(ClassReflection $class, string $propertyName): ?array
    {
        $key = $class->getName() . '::$' . $propertyName;
        if (isset($this->resolving[$key])) {
            return null;
        }

        $this->resolving[$key] = true;

        try {
            $lineage = [$class->getName(), ...$class->getParentClassesNames()];

            foreach ($lineage as $ancestor) {
                if (! $this->reflectionProvider->hasClass($ancestor)) {
                    continue;
                }

                if (isset($this->reflectionProvider->getClass($ancestor)->getPropertyTags()[$propertyName])) {
                    // Declared by the receiver's own lineage: PHPStan's answer, not this one.
                    return null;
                }
            }

            foreach ($lineage as $ancestor) {
                foreach ($this->classes->extensionsOf($ancestor) as $extension) {
                    if (in_array($extension, $lineage, true) || ! $this->reflectionProvider->hasClass($extension)) {
                        continue;
                    }

                    $reflection = $this->reflectionProvider->getClass($extension);
                    $tag = $reflection->getPropertyTags()[$propertyName] ?? null;
                    if ($tag !== null) {
                        return [$reflection, $tag];
                    }
                }
            }

            return null;
        } finally {
            unset($this->resolving[$key]);
        }
    }
}
