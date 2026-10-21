<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Type;

/**
 * XenForo's short class identifier, `Vendor\AddOn:Name`, as each container shortcut expands it -
 * \XF::stringToClass() with the formatter that shortcut uses. A string without a colon is
 * already a class name and is left to XenForo's own class-string annotations.
 */
final class Shortcut
{
    public const ENTITY = 'entity';
    public const FINDER = 'finder';
    public const REPOSITORY = 'repository';
    public const SERVICE = 'service';
    public const DATA = 'data';
    public const PLUGIN = 'plugin';
    public const BEHAVIOR = 'behavior';
    public const JOB = 'job';
    public const SEARCHER = 'searcher';
    public const FILTERER = 'filterer';
    public const AUTH = 'auth';
    public const HELPER = 'helper';
    public const VALIDATOR = 'validator';
    public const CRITERIA = 'criteria';
    public const NOTIFIER = 'notifier';

    /** The formatter each shortcut passes to \XF::stringToClass(), from XenForo 2.3.13. */
    private const FORMATTERS = [
        self::ENTITY => '%s\Entity\%s',
        self::FINDER => '%s\Finder\%s',
        self::REPOSITORY => '%s\Repository\%s',
        self::SERVICE => '%s\Service\%s',
        self::DATA => '%s\Data\%s',
        self::PLUGIN => '%s\ControllerPlugin\%s',
        self::BEHAVIOR => '%s\Behavior\%s',
        self::JOB => '%s\Job\%s',
        self::SEARCHER => '%s\Searcher\%s',
        self::FILTERER => '%s\Filterer\%s',
        self::AUTH => '%s\Authentication\%s',
        self::HELPER => '%s\Helper\%s',
        self::VALIDATOR => '%s\Validator\%s',
        self::CRITERIA => '%s\Criteria\%s',
        self::NOTIFIER => '%s\Notifier\%s',
    ];

    /**
     * A controller plugin named with two colons - Vendor\AddOn:Admin:Name - lives under the
     * application type: XF\Mvc\Controller::plugin() switches formatter on the count.
     */
    private const PLUGIN_WITH_APP = '%s\%s\ControllerPlugin\%s';

    /**
     * The class an identifier names for a shortcut of this kind, or null for an argument that is
     * not the short form.
     */
    public static function classFor(string $identifier, string $kind): ?string
    {
        if (! str_contains($identifier, ':')) {
            return null;
        }

        $parts = explode(':', $identifier, 3);
        $prefix = $parts[0];

        if ($kind === self::PLUGIN && count($parts) === 3) {
            return sprintf(self::PLUGIN_WITH_APP, $prefix, $parts[1], $parts[2]);
        }

        // Three parts to a two-part formatter: XenForo keeps the prefix and the last part.
        $suffix = $parts[2] ?? $parts[1];

        return sprintf(self::FORMATTERS[$kind], $prefix, $suffix);
    }
}
