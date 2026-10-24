<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

/**
 * $templater->func('avatar', [...]) is how a test, or a renderer outside a template, calls a
 * template function. XenForo declares no return type, and the body's last line is
 * `$escape ? $this->escape($value) : $value`: escaped, the result is a string whatever the
 * function returned; unescaped, it is whatever the function returned. So the call is a string
 * when $escape is omitted or true, and is left to PHPStan when it is anything else.
 */
final class FuncReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @param  class-string  $className  the templater class, from extension.neon
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
        return $methodReflection->getName() === 'func';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();
        if (isset($args[2]) && ! $scope->getType($args[2]->value)->toBoolean()->isTrue()->yes()) {
            return null;
        }

        return new StringType();
    }
}
