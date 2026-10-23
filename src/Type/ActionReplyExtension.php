<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Every controller action returns a reply: the dispatcher rejects anything else. XenForo declares
 * no return type on them, so `return parent::actionIndex()`, the commonest line in a controller
 * extension, is mixed. This answers AbstractReply for an action* method that declares nothing,
 * through $this-> and parent:: alike, and leaves one that declares a return type alone.
 */
final class ActionReplyExtension implements DynamicMethodReturnTypeExtension, DynamicStaticMethodReturnTypeExtension
{
    private const REPLY = 'XF\Mvc\Reply\AbstractReply';

    /**
     * @param  class-string  $className  the controller base class, from extension.neon
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
        return $this->isUndeclaredAction($methodReflection);
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $this->isUndeclaredAction($methodReflection);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        return new ObjectType(self::REPLY);
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): Type
    {
        return new ObjectType(self::REPLY);
    }

    /**
     * An action by name, with no return type of its own: a declared one, native or in a
     * docblock, is XenForo's or the add-on's to keep.
     */
    private function isUndeclaredAction(MethodReflection $method): bool
    {
        if (preg_match('/^action[A-Z0-9]/', $method->getName()) !== 1) {
            return false;
        }

        $variants = $method->getVariants();
        if (count($variants) !== 1) {
            return false;
        }

        $return = $variants[0]->getReturnType();

        return $return instanceof MixedType && ! $return->isExplicitMixed();
    }
}
