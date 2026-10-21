<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

use Hampel\XenForo\PHPStan\Classes;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * The return type of a container shortcut from the identifier it was given, shared by the
 * instance and static extensions.
 */
final class ShortcutTypes
{
    /**
     * Every method this package types, by the class that declares it. XenForo 2.3 annotates
     * each with @template T and class-string<T>, which the short identifier defeats - so only
     * the identifier form is answered here, and a class-string argument is left to XenForo's
     * own annotation. [kind, nullable]: find() and findOne() answer null for no row.
     *
     * @var array<string, array<string, array{string, bool}>>
     */
    public const METHODS = [
        'XF' => [
            'finder' => [Shortcut::FINDER, false],
            'repository' => [Shortcut::REPOSITORY, false],
            'service' => [Shortcut::SERVICE, false],
        ],
        'XF\App' => [
            'find' => [Shortcut::ENTITY, true],
            'finder' => [Shortcut::FINDER, false],
            'repository' => [Shortcut::REPOSITORY, false],
            'service' => [Shortcut::SERVICE, false],
            'data' => [Shortcut::DATA, false],
            'job' => [Shortcut::JOB, true],
            'searcher' => [Shortcut::SEARCHER, false],
            'filterer' => [Shortcut::FILTERER, false],
            'auth' => [Shortcut::AUTH, false],
            'helper' => [Shortcut::HELPER, false],
            'validator' => [Shortcut::VALIDATOR, false],
            'criteria' => [Shortcut::CRITERIA, false],
            'notifier' => [Shortcut::NOTIFIER, false],
        ],
        'XF\Mvc\Controller' => [
            'plugin' => [Shortcut::PLUGIN, false],
            'service' => [Shortcut::SERVICE, false],
            'data' => [Shortcut::DATA, false],
            'finder' => [Shortcut::FINDER, false],
            'repository' => [Shortcut::REPOSITORY, false],
            'assertRecordExists' => [Shortcut::ENTITY, false],
            'assertRecordExistsByUnique' => [Shortcut::ENTITY, false],
            'assertViewableRecord' => [Shortcut::ENTITY, false],
        ],
        'XF\Api\Controller\AbstractController' => [
            'assertViewableApiRecord' => [Shortcut::ENTITY, false],
        ],
        'XF\Mvc\Entity\Manager' => [
            'create' => [Shortcut::ENTITY, false],
            'instantiateEntity' => [Shortcut::ENTITY, false],
            'find' => [Shortcut::ENTITY, true],
            'findCached' => [Shortcut::ENTITY, true],
            'findOne' => [Shortcut::ENTITY, true],
            'getFinder' => [Shortcut::FINDER, false],
            'getRepository' => [Shortcut::REPOSITORY, false],
        ],
        'XF\Mvc\Entity\Repository' => [
            'finder' => [Shortcut::FINDER, false],
            'repository' => [Shortcut::REPOSITORY, false],
        ],
        'XF\Mvc\Entity\Entity' => [
            'finder' => [Shortcut::FINDER, false],
            'repository' => [Shortcut::REPOSITORY, false],
            'getBehavior' => [Shortcut::BEHAVIOR, false],
        ],
        'XF\Service\AbstractService' => [
            'repository' => [Shortcut::REPOSITORY, false],
            'finder' => [Shortcut::FINDER, false],
            'findOne' => [Shortcut::ENTITY, true],
            'service' => [Shortcut::SERVICE, false],
        ],
        'XF\Widget\AbstractWidget' => [
            'repository' => [Shortcut::REPOSITORY, false],
            'finder' => [Shortcut::FINDER, false],
            'findOne' => [Shortcut::ENTITY, true],
            'service' => [Shortcut::SERVICE, false],
        ],
    ];

    /**
     * An entity with no finder class of its own gets the generic one - Manager::getFinder()
     * falls back the same way.
     */
    private const GENERIC_FINDER = 'XF\Mvc\Entity\Finder';

    public function __construct(
        private readonly Classes $classes,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    /**
     * The type for a shortcut given this argument, or null to leave PHPStan's own answer standing:
     * the argument is not a constant string in the identifier form, or names a class the forum
     * does not have. $file is the file being analysed, which decides whose extension applies.
     */
    public function typeFor(string $kind, bool $nullable, Type $argument, string $file): ?Type
    {
        $strings = $argument->getConstantStrings();
        if ($strings === []) {
            return null;
        }

        $types = [];
        foreach ($strings as $string) {
            $class = Shortcut::classFor($string->getValue(), $kind);
            if ($class === null) {
                return null;
            }

            $named = $this->classNamed($class, $kind);
            if ($named === null) {
                return null;
            }

            // Within the add-on that extends the class, the shortcut returns the extension: at
            // runtime it is what comes back, and it is the only type under which a method the
            // extension overrides with a different signature is called correctly. Elsewhere the
            // core class, with the extension's added methods reachable through it.
            $own = $this->classes->extensionOwnedBy($this->classes->resolveAlias($named), $file);
            if ($own !== null && $this->reflectionProvider->hasClass($own)) {
                $named = $own;
            }

            $types[] = new ObjectType($named);
        }

        $type = TypeCombinator::union(...$types);

        return $nullable ? TypeCombinator::addNull($type) : $type;
    }

    /**
     * The class to type the result as. A legacy name is kept when PHPStan knows it - the
     * generated alias stub declares it as a subclass of the renamed class, so a value typed
     * to it satisfies a parameter or return type written either way, which is what an add-on
     * supporting 2.2 and 2.3 has. Resolving to the renamed class instead would fail the
     * add-on's own `: \XF\Finder\User` return type. An unknown finder is the generic one.
     */
    private function classNamed(string $class, string $kind): ?string
    {
        foreach ([$class, $this->classes->resolveAlias($class)] as $candidate) {
            if ($this->reflectionProvider->hasClass($candidate)) {
                return $candidate;
            }
        }

        return $kind === Shortcut::FINDER ? self::GENERIC_FINDER : null;
    }
}
