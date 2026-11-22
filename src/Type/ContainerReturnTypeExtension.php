<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * A read from a container: $app->container('key'), $app['key'], $container['key'], and inside a
 * sub-container $this->container['key'] and, from outside it, $subContainer['key'].
 *
 * $app->container() with no key is the container itself. XenForo documents the method as
 * `Container|mixed`, which PHPStan reads as mixed on both paths, so an add-on that reaches the
 * container to register or inspect an entry calls every method on it undefined; this answers
 * XF\Container when no key is given, or the key is null.
 *
 * With a key the result is whatever the entry holds, which is a fact about the add-on that
 * registered it and not about the class. It is typed only where the add-on has declared it, see
 * ContainerKeys, and left to PHPStan everywhere else. A sub-container keeps its own entries in
 * a container of its own, under keys that mean nothing outside it, so those are declared per
 * sub-container class and answered for `$this->container` inside that class and for the
 * sub-container read as an array; any other container is taken to be the application's.
 *
 * One service per class, because an array read reaches this through the class's offsetGet().
 */
final class ContainerReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    private const CONTAINER = 'XF\Container';

    private const SUB_CONTAINER = 'XF\SubContainer\AbstractSubContainer';

    /**
     * @param  class-string  $className  the application, container or sub-container class, from extension.neon
     */
    public function __construct(
        private readonly string $className,
        private readonly ContainerKeys $keys,
    ) {
    }

    /** @return class-string */
    public function getClass(): string
    {
        return $this->className;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), ['container', 'offsetGet'], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();
        $bare = $args === [] || $scope->getType($args[0]->value)->isNull()->yes();

        if ($methodReflection->getName() === 'container') {
            if ($bare) {
                return new ObjectType(self::CONTAINER);
            }
        } elseif ($bare) {
            return null;
        }

        $keys = $scope->getType($args[0]->value)->getConstantStrings();
        if ($keys === []) {
            return null;
        }

        $lineage = $this->subContainerRead($methodCall->var, $scope);

        $types = [];
        foreach ($keys as $key) {
            $type = $lineage === null
                ? $this->keys->inApp($key->getValue())
                : $this->keys->inSubContainer($lineage, $key->getValue());
            if ($type === null) {
                return null;
            }
            $types[] = $type;
        }

        return TypeCombinator::union(...$types);
    }

    /**
     * The sub-container classes whose keys this read is of, nearest first, or null when the
     * container read is the application's.
     *
     * @return list<string>|null
     */
    private function subContainerRead(Expr $receiver, Scope $scope): ?array
    {
        // $subContainer['key'], or its offsetGet(): the receiver is the sub-container.
        foreach ($scope->getType($receiver)->getObjectClassReflections() as $class) {
            $lineage = $this->lineage($class);
            if (in_array(self::SUB_CONTAINER, $lineage, true)) {
                return $lineage;
            }
        }

        // $this->container['key'], written inside a sub-container: its own container.
        $inside = $scope->getClassReflection();
        if (
            $inside !== null
            && $receiver instanceof PropertyFetch
            && $receiver->var instanceof Variable
            && $receiver->var->name === 'this'
            && $receiver->name instanceof Identifier
            && $receiver->name->name === 'container'
        ) {
            $lineage = $this->lineage($inside);
            if (in_array(self::SUB_CONTAINER, $lineage, true)) {
                return $lineage;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function lineage(ClassReflection $class): array
    {
        $names = [$class->getName()];
        foreach ($class->getParents() as $parent) {
            $names[] = $parent->getName();
        }

        return $names;
    }
}
