<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Generator;

final class Summary
{
    /**
     * @param  list<string>  $skippedAliases
     */
    public function __construct(
        public readonly int $versionId,
        public readonly int $aliases,
        public readonly int $abstractAliases,
        public readonly array $skippedAliases,
        public readonly int $extensions,
        public readonly int $legacyExtensions,
        public readonly int $extendedClasses,
        public readonly int $inactiveExtensions,
        public readonly int $fakeBaseExtensions,
    ) {
    }
}
