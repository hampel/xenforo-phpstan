<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

/**
 * $this->repository('Vendor\AddOn:Name'), and the other shortcuts, typed from the identifier.
 * Registered once per class in ShortcutTypes::METHODS - extension.neon names each.
 */
final class ContainerShortcutExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @param  class-string  $className
     */
    public function __construct(
        private readonly ShortcutTypes $types,
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
        return isset(ShortcutTypes::METHODS[$this->className][$methodReflection->getName()]);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();
        if ($args === []) {
            return null;
        }

        [$kind, $nullable] = ShortcutTypes::METHODS[$this->className][$methodReflection->getName()];

        return $this->types->typeFor($kind, $nullable, $scope->getType($args[0]->value));
    }
}
