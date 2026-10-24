<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * $app->container() with no key is the container itself; with a key it is whatever the key
 * holds. XenForo documents the method as `Container|mixed`, which PHPStan reads as mixed on both
 * paths, so an add-on that reaches the container to register or inspect an entry calls every
 * method on it undefined. This answers XF\Container when no key is given, or the key is null,
 * and leaves a keyed call to PHPStan: an entry's type is a fact about the install, not the class.
 */
final class ContainerReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    private const CONTAINER = 'XF\Container';

    /**
     * @param  class-string  $className  the application class, from extension.neon
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
        return $methodReflection->getName() === 'container';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();
        if ($args !== [] && ! $scope->getType($args[0]->value)->isNull()->yes()) {
            return null;
        }

        return new ObjectType(self::CONTAINER);
    }
}
