<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Tests\Generator;

use Hampel\XenForo\PHPStan\Generator\Command;
use Hampel\XenForo\PHPStan\Tests\Fixture;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    private string $out;

    protected function setUp(): void
    {
        $this->out = Fixture::temp('command');
    }

    protected function tearDown(): void
    {
        Fixture::remove($this->out);
    }

    /** @return array{int, string, string} exit code, stdout, stderr */
    private function runCommand(string ...$args): array
    {
        $stdout = fopen('php://memory', 'w+');
        $stderr = fopen('php://memory', 'w+');
        $this->assertNotFalse($stdout);
        $this->assertNotFalse($stderr);

        $code = (new Command())->run(['xenforo-phpstan', ...array_values($args)], $stdout, $stderr);
        rewind($stdout);
        rewind($stderr);

        return [$code, (string) stream_get_contents($stdout), (string) stream_get_contents($stderr)];
    }

    public function testGenerateReportsWhatItWroteAndWhere(): void
    {
        [$code, $stdout, $stderr] = $this->runCommand('generate', '--root=' . Fixture::forum(), '--out=' . $this->out);

        $this->assertSame(0, $code, $stderr);
        $this->assertSame('', $stderr);
        $this->assertStringContainsString('XenForo 2031370 at ' . Fixture::forum(), $stdout);
        $this->assertStringContainsString('legacy-aliases.stub.php: 6 legacy class names (1 abstract), 2 skipped as undeclarable', $stdout);
        $this->assertStringContainsString('class-extensions.stub.php: 3 class extensions of 2 classes, 1 registered on a legacy name', $stdout);
        $this->assertStringContainsString('written to ' . $this->out, $stdout);
        $this->assertFileExists($this->out . '/classes.php');
    }

    public function testADirectoryWithoutXenForoInItIsRefusedByName(): void
    {
        [$code, , $stderr] = $this->runCommand('generate', '--root=' . $this->out);

        $this->assertSame(1, $code);
        $this->assertStringContainsString($this->out . ' is not a XenForo root', $stderr);
    }

    public function testAnythingButGenerateIsUsage(): void
    {
        [$code, , $stderr] = $this->runCommand('frobnicate');
        $this->assertSame(2, $code);
        $this->assertStringContainsString('Usage: xenforo-phpstan generate', $stderr);

        [$code, $stdout] = $this->runCommand('--help');
        $this->assertSame(0, $code);
        $this->assertStringContainsString('Usage: xenforo-phpstan generate', $stdout);
    }
}
