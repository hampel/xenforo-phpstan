<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Reflection;

use PHPStan\Reflection\Annotations\AnnotationPropertyReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * XF\AddOn\AddOn, what Manager::getById() returns, answers `$addOn->key` through __get() and
 * offsetGet(): nine keys from its own getters, then any column of the installed XF\Entity\AddOn,
 * then the add-on's addon.json. Every add-on that reports its own version reads `$addOn->title`
 * or `$addOn->version_string`, and PHPStan reports each as undefined. This answers for the nine
 * keys with their getters' types and for the rest with the entity's own @property docblock, so
 * that a mistyped key is still reported - which a crate would not do. A key that exists only in
 * addon.json is not offered: read it through getJson().
 */
final class AddOnPropertiesExtension implements PropertiesClassReflectionExtension
{
    private const ADD_ON = 'XF\AddOn\AddOn';

    private const ENTITY = 'XF\Entity\AddOn';

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return $this->isAddOn($classReflection) && $this->typeOf($propertyName) !== null;
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        $type = $this->typeOf($propertyName);
        if ($type === null) {
            throw new \LogicException(sprintf('%s::$%s was not offered by this extension.', $classReflection->getName(), $propertyName));
        }

        // Read-only: offsetSet() throws.
        return new AnnotationPropertyReflection($propertyName, $classReflection, $type, $type, true, false);
    }

    private function isAddOn(ClassReflection $class): bool
    {
        return in_array(self::ADD_ON, [$class->getName(), ...$class->getParentClassesNames()], true);
    }

    /**
     * The nine keys offsetGet() answers itself, typed from their getters as 2.3.13 writes them;
     * then the installed entity's columns, as its docblock declares them.
     */
    private function typeOf(string $key): ?Type
    {
        $string = new StringType();
        $strings = TypeCombinator::intersect(new ArrayType(new IntegerType(), $string), new AccessoryArrayListType());

        $own = match ($key) {
            'addon' => TypeCombinator::addNull(new ObjectType(self::ENTITY)),
            'addon_id', 'addon_id_url', 'json_version_string' => $string,
            'json_version_id' => new IntegerType(),
            'json' => new ArrayType($string, new MixedType()),
            'missing_files', 'additional_files' => $strings,
            'legacy_addon_id' => TypeCombinator::addNull($string),
            default => null,
        };
        if ($own !== null) {
            return $own;
        }

        if (! $this->reflectionProvider->hasClass(self::ENTITY)) {
            return null;
        }

        // The entity's own @property lines, not hasProperty(): with crates.neon included the
        // entity is an object crate and would answer yes to any name, which is exactly what this
        // extension exists not to do.
        $tag = $this->reflectionProvider->getClass(self::ENTITY)->getPropertyTags()[$key] ?? null;

        return $tag?->getReadableType();
    }
}
