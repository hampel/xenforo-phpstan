<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

/**
 * $this->filter('id', 'uint') on a controller, and the same on the request, typed from the filter
 * string. Registered once per class in extension.neon. Where FilterTypes cannot say, null leaves
 * the stub's own answer standing.
 */
final class FilterReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @param  class-string  $className
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
        return $methodReflection->getName() === 'filter';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();
        if ($args === []) {
            return null;
        }

        return FilterTypes::resultOf(
            $scope->getType($args[0]->value),
            isset($args[1]) ? $scope->getType($args[1]->value) : null,
        );
    }
}
