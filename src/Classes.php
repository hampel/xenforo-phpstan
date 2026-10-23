<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan;

use RuntimeException;

/**
 * What `xenforo-phpstan generate` learned from the forum: the legacy name XenForo 2.3
 * aliases for each renamed class, and the class extensions every installed add-on
 * registers. Both extensions read it through here, lazily, so a forum that has moved on
 * since the files were generated is reported once and plainly rather than as a flood of
 * undefined-method errors naming classes that were renamed.
 */
final class Classes
{
    /**
     * The layout of classes.php. Raised when a reader needs a field an older generator did not
     * write, so that the file is regenerated rather than misread: 2 records the add-on each
     * class extension belongs to.
     */
    public const FORMAT = 2;

    /** @var array{xenforo: array{versionId: int, root: string}, generated?: string, aliases: array<string, string>, extensions: array<string, list<array{class: string, addon: string}>>}|null */
    private ?array $data = null;

    private readonly string $root;

    public function __construct(
        private readonly string $file,
        string $root,
    ) {
        // Stated plainly in a message: from an add-on the configured root is four `..` segments.
        $this->root = realpath($root) ?: $root;
    }

    /**
     * The class XenForo resolves a legacy name to, or the name itself when it is not an alias.
     */
    public function resolveAlias(string $class): string
    {
        $class = ltrim($class, '\\');

        return $this->aliases()[$class] ?? $class;
    }

    /**
     * The classes that extend a core class, under the name XenForo registers them against.
     *
     * @return list<string>
     */
    public function extensionsOf(string $class): array
    {
        return array_column($this->extensions()[ltrim($class, '\\')] ?? [], 'class');
    }

    /**
     * The extension of a core class registered by the add-on a file belongs to, or null. A file
     * belongs to the add-on whose directory under src/addons contains it - its tests included.
     */
    public function extensionOwnedBy(string $class, string $file): ?string
    {
        foreach ($this->extensions()[ltrim($class, '\\')] ?? [] as $extension) {
            if (str_starts_with($file, $this->root . '/src/addons/' . $extension['addon'] . '/')) {
                return $extension['class'];
            }
        }

        return null;
    }

    /** @return array<string, string> alias => class */
    public function aliases(): array
    {
        return $this->load()['aliases'];
    }

    /** @return array<string, list<array{class: string, addon: string}>> core class => its extensions */
    public function extensions(): array
    {
        return $this->load()['extensions'];
    }

    public function versionId(): int
    {
        return $this->load()['xenforo']['versionId'];
    }

    /**
     * When the set was generated, as the generator stated it, or null for a file written before
     * the date was recorded. Loading it applies the staleness check, so a stale set throws here
     * as it does everywhere else.
     */
    public function generated(): ?string
    {
        return $this->load()['generated'] ?? null;
    }

    /**
     * The versionId declared in a forum's src/XF.php, read without loading it. Null when the
     * file is absent or does not carry the line.
     */
    public static function forumVersionId(string $root): ?int
    {
        $source = @file_get_contents($root . '/src/XF.php');
        if ($source === false || preg_match('/public\s+static\s+\$versionId\s*=\s*(\d+)\s*;/', $source, $m) !== 1) {
            return null;
        }

        return (int) $m[1];
    }

    /**
     * @return array{xenforo: array{versionId: int, root: string}, generated?: string, aliases: array<string, string>, extensions: array<string, list<array{class: string, addon: string}>>}
     */
    private function load(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        if (! is_file($this->file)) {
            throw new RuntimeException(sprintf(
                'hampel/xenforo-phpstan: %s does not exist. Run `vendor/bin/xenforo-phpstan generate` against the forum at %s, and after every XenForo upgrade.',
                $this->file,
                $this->root,
            ));
        }

        $data = require $this->file;
        $this->assertGenerated($data);

        $forum = self::forumVersionId($this->root);
        if ($forum !== null && $forum !== $data['xenforo']['versionId']) {
            throw new RuntimeException(sprintf(
                'hampel/xenforo-phpstan: %s was generated for XenForo %d, but the forum at %s is %d. Run `vendor/bin/xenforo-phpstan generate` again.',
                $this->file,
                $data['xenforo']['versionId'],
                $this->root,
                $forum,
            ));
        }

        $this->data = $data;

        return $data;
    }

    /**
     * @phpstan-assert array{xenforo: array{versionId: int, root: string}, generated?: string, aliases: array<string, string>, extensions: array<string, list<array{class: string, addon: string}>>} $data
     */
    private function assertGenerated(mixed $data): void
    {
        if (is_array($data) && ($data['format'] ?? 1) !== self::FORMAT) {
            throw new RuntimeException(sprintf(
                'hampel/xenforo-phpstan: %s was written by an older release of this package. Run `vendor/bin/xenforo-phpstan generate` again against the forum at %s.',
                $this->file,
                $this->root,
            ));
        }

        if (is_array($data)
            && is_array($data['xenforo'] ?? null)
            && is_int($data['xenforo']['versionId'] ?? null)
            && is_string($data['xenforo']['root'] ?? null)
            && (! isset($data['generated']) || is_string($data['generated']))
            && is_array($data['aliases'] ?? null)
            && is_array($data['extensions'] ?? null)) {
            return;
        }

        throw new RuntimeException(sprintf(
            'hampel/xenforo-phpstan: %s is not a file `xenforo-phpstan generate` wrote. Run it again against the forum at %s.',
            $this->file,
            $this->root,
        ));
    }
}
