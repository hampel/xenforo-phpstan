<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * $finder->pluckFrom('user_id') replaces each fetched entity with that column, or with what a
 * closure returns, so what fetch() and fetchOne() then yield is no longer the entity the finder
 * was made for. XenForo declares the method as returning the finder, which keeps the entity type;
 * this returns the base finder over the plucked value: the entity's own type for the column,
 * read through the entity as PHPStan sees it, so a column an extension adds counts; the closure's
 * return type for a closure; mixed where neither can be read.
 */
final class PluckReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @param  class-string  $className  the finder base class, from extension.neon
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
        return $methodReflection->getName() === 'pluckFrom';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();
        if ($args === []) {
            return null;
        }

        $entity = $scope->getType($methodCall->var)->getTemplateType($this->className, 'T');
        $plucked = $scope->getType($args[0]->value);

        return new GenericObjectType($this->className, [$this->valueOf($entity, $plucked, $scope)]);
    }

    private function valueOf(Type $entity, Type $plucked, Scope $scope): Type
    {
        $types = [];

        foreach ($plucked->getConstantStrings() as $column) {
            if (! $entity->hasProperty($column->getValue())->yes()) {
                return new MixedType();
            }

            $types[] = $entity->getProperty($column->getValue(), $scope)->getReadableType();
        }

        if ($types === [] && $plucked->isCallable()->yes()) {
            foreach ($plucked->getCallableParametersAcceptors($scope) as $acceptor) {
                $types[] = $acceptor->getReturnType();
            }
        }

        return $types === [] ? new MixedType() : TypeCombinator::union(...$types);
    }
}
