<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;

/**
 * \XF::repository('Vendor\AddOn:Name'), \XF::finder() and \XF::service(), typed from the
 * identifier.
 */
final class StaticContainerShortcutExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function __construct(
        private readonly ShortcutTypes $types,
    ) {
    }

    /** @return class-string */
    public function getClass(): string
    {
        return \XF::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return isset(ShortcutTypes::METHODS['XF'][$methodReflection->getName()]);
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();
        if ($args === []) {
            return null;
        }

        [$kind, $nullable] = ShortcutTypes::METHODS['XF'][$methodReflection->getName()];

        return $this->types->typeFor($kind, $nullable, $scope->getType($args[0]->value), $scope->getFile());
    }
}
