<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Generator;

use Hampel\XenForo\PHPStan\Classes;
use Throwable;

/**
 * `xenforo-phpstan generate [--check] [--root=DIR] [--out=DIR]`. Plain argv: this is the only
 * command, and a console component would be a dependency on every add-on's dev install for it.
 */
final class Command
{
    private const USAGE = <<<'TXT'
        Usage: xenforo-phpstan generate [--check] [--root=DIR] [--out=DIR]

        Writes the files PHPStan needs to analyse an add-on against a XenForo install:
        the legacy class names XenForo 2.3 aliases at runtime, and the XFCP_ parent of
        every class extension the install declares. Run it once per forum, and again
        after upgrading XenForo or changing a class extension.

          --check     write nothing; report whether the set already there is one PHPStan
                      will accept for this forum. Exit 0 when it is current, 1 when it
                      is missing, stale or from an older release, with the reason
          --root=DIR  the forum root. Default: XF_ROOT from the environment, else four
                      directories above the working directory, where a forum is from
                      an add-on at <root>/src/addons/<Vendor>/<AddOn>
          --out=DIR   where to write, or with --check where to read. Default:
                      <root>/internal_data/phpstan, which is what extension.neon reads
                      unless `xenforo.generated` says otherwise

        TXT;

    /**
     * @param  list<string>  $argv
     * @param  resource  $stdout
     * @param  resource  $stderr
     */
    public function run(array $argv, $stdout, $stderr): int
    {
        $options = [];
        $words = [];
        $check = false;
        foreach (array_slice($argv, 1) as $arg) {
            if (preg_match('/^--(root|out)=(.+)$/', $arg, $m) === 1) {
                $options[$m[1]] = $m[2];
            } elseif ($arg === '--check') {
                $check = true;
            } elseif ($arg === '-h' || $arg === '--help') {
                fwrite($stdout, self::USAGE);

                return 0;
            } else {
                $words[] = $arg;
            }
        }

        if ($words !== ['generate']) {
            fwrite($stderr, self::USAGE);

            return 2;
        }

        $root = $options['root'] ?? (getenv('XF_ROOT') ?: getcwd() . '/../../../..');
        $resolved = realpath($root);
        if ($resolved === false || ! Generator::isForum($resolved)) {
            fwrite($stderr, sprintf("%s is not a XenForo root: no src/XF.php. Pass --root=DIR or set XF_ROOT.\n", $root));

            return 1;
        }

        $out = $options['out'] ?? $resolved . '/internal_data/phpstan';

        if ($check) {
            return $this->check($resolved, $out, $stdout, $stderr);
        }

        try {
            $summary = (new Generator($resolved))->generate($out);
        } catch (Throwable $e) {
            fwrite($stderr, $e->getMessage() . "\n");

            return 1;
        }

        fwrite($stdout, sprintf("XenForo %d at %s\n", $summary->versionId, $resolved));
        fwrite($stdout, sprintf(
            "  %s: %d legacy class names (%d abstract), %d skipped as undeclarable\n",
            Generator::ALIASES_STUB,
            $summary->aliases,
            $summary->abstractAliases,
            count($summary->skippedAliases),
        ));
        fwrite($stdout, sprintf(
            "  %s: %d class extensions of %d classes, %d registered on a legacy name\n",
            Generator::EXTENSIONS_STUB,
            $summary->extensions,
            $summary->extendedClasses,
            $summary->legacyExtensions,
        ));
        fwrite($stdout, sprintf("  %s\n", Generator::CLASSES));
        fwrite($stdout, sprintf("written to %s\n", $out));

        return 0;
    }

    /**
     * The question PHPStan asks on first use, asked without writing: several add-ons on one
     * forum share the set, so a session wanting to know it is current should not have to
     * regenerate it under the others to find out. The loader's own refusals are the answer.
     *
     * @param  resource  $stdout
     * @param  resource  $stderr
     */
    private function check(string $root, string $out, $stdout, $stderr): int
    {
        $classes = new Classes($out . '/' . Generator::CLASSES, $root);

        try {
            $versionId = $classes->versionId();
            $generated = $classes->generated();
            $aliases = count($classes->aliases());
            $extended = $classes->extensions();
        } catch (Throwable $e) {
            fwrite($stderr, $e->getMessage() . "\n");

            return 1;
        }

        fwrite($stdout, sprintf("XenForo %d at %s\n", $versionId, $root));
        fwrite($stdout, sprintf(
            "  %s is current%s: %d aliases, %d class extensions of %d classes\n",
            $out . '/' . Generator::CLASSES,
            $generated === null ? '' : ', generated ' . $generated,
            $aliases,
            array_sum(array_map('count', $extended)),
            count($extended),
        ));

        return 0;
    }
}
