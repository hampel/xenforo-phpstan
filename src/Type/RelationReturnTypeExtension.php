<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * $user->getRelationOrDefault('Auth') typed from the receiver's own `@property-read UserAuth|null
 * $Auth`: XenForo's entities declare every relation that way, and an add-on's extension declares
 * its own relations the same way, so the name says what comes back. getRelation() keeps the
 * declared nullability; getRelationOrDefault() creates the default, so null is removed. A name
 * the receiver does not declare, or a crate's mixed, answers null and the call stays undeclared:
 * returning Entity instead made every column read on the result a report from level 2.
 */
final class RelationReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    private const METHODS = ['getRelation' => true, 'getRelationOrDefault' => false];

    /**
     * @param  class-string  $className  the entity base class, from extension.neon
     */
    public function __construct(
        private readonly string $className,
    ) {
    }

    /** @return class-string */
    public function getClass(): string
    {
        return $this->className;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return isset(self::METHODS[$methodReflection->getName()]);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();
        if ($args === []) {
            return null;
        }

        $names = $scope->getType($args[0]->value)->getConstantStrings();
        if ($names === []) {
            return null;
        }

        $receiver = $scope->getType($methodCall->var);
        $nullable = self::METHODS[$methodReflection->getName()];
        $types = [];
        foreach ($names as $name) {
            if (! $receiver->hasProperty($name->getValue())->yes()) {
                return null;
            }

            $type = $receiver->getProperty($name->getValue(), $scope)->getReadableType();
            if ($type->isSuperTypeOf(new MixedType())->yes()) {
                // A crate answers mixed for any name; that is no better than undeclared.
                return null;
            }

            $types[] = $nullable ? $type : TypeCombinator::removeNull($type);
        }

        return TypeCombinator::union(...$types);
    }
}
