<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Generator;

/**
 * PHP source for PHPStan to scan: declarations grouped by namespace, under a header that says
 * what they were generated from. Declarations are counted as they are emitted, so the count
 * in the header is the count in the file.
 */
final class Stub
{
    /** @var array<string, list<string>> namespace => declarations */
    private array $declarations = [];

    private int $count = 0;

    public function add(string $namespace, string $declaration): void
    {
        $this->declarations[$namespace][] = $declaration;
        $this->count++;
    }

    public function count(): int
    {
        return $this->count;
    }

    /**
     * @param  list<string>  $header  lines, without comment markers
     */
    public function render(array $header): string
    {
        $php = "<?php\n\n";
        foreach ($header as $line) {
            $php .= rtrim('// ' . $line) . "\n";
        }
        $php .= "\n";

        ksort($this->declarations);
        foreach ($this->declarations as $namespace => $declarations) {
            sort($declarations);
            $php .= "namespace {$namespace}\n{\n\t" . implode("\n\t", $declarations) . "\n}\n\n";
        }

        return rtrim($php) . "\n";
    }
}
