<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Tests;

use PHPUnit\Framework\TestCase;

/**
 * The whole package, end to end: generate against the fixture forum, write the phpstan.neon an
 * add-on would, run the real PHPStan from this package's vendor/bin, and read the result. The
 * fixture controller makes every kind of call this package exists to type, and two that are
 * genuinely wrong; the run passes when exactly those two are reported.
 */
final class AnalysisTest extends TestCase
{
    private string $generated;

    private string $work;

    protected function setUp(): void
    {
        [$this->generated] = Fixture::generate();
        $this->work = Fixture::temp('analysis');
    }

    protected function tearDown(): void
    {
        Fixture::remove($this->generated);
        Fixture::remove($this->work);
    }

    public function testOnlyTheTwoGenuineErrorsAreReported(): void
    {
        $result = $this->analyse($this->addonConfig());

        $this->assertArrayHasKey('files', $result, 'PHPStan analysed nothing: ' . json_encode($result));

        $messages = [];
        foreach ($result['files'] ?? [] as $file => $errors) {
            foreach ($errors['messages'] as $error) {
                $messages[] = basename($file) . ':' . $error['line'] . ' ' . $error['message'];
            }
        }

        $this->assertSame([
            'Demo.php:34 Call to an undefined method Acme\Demo\Repository\Widget::noSuchMethod().',
            'Demo.php:35 Call to an undefined method XF\Entity\User::noSuchMethodEither().',
        ], $messages);
    }

    public function testWithoutThisPackageTheSameCodeIsAFlood(): void
    {
        // The control: scanning the forum but with no stubs and no extensions registered.
        $result = $this->analyse(<<<NEON
            parameters:
                level: 5
                tmpDir: {$this->work}/cache
                paths:
                    - {$this->addon()}
                excludePaths:
                    analyseAndScan:
                        - {$this->addon()}/vendor
                scanDirectories:
                    - {$this->forum()}/src/XF
                    - {$this->forum()}/src/addons
                scanFiles:
                    - {$this->forum()}/src/XF.php
            NEON);

        $this->assertArrayHasKey('files', $result);
        $this->assertGreaterThan(10, $result['totals']['file_errors']);
    }

    private function addonConfig(): string
    {
        $package = dirname(__DIR__);

        return <<<NEON
            # What an add-on's own phpstan.neon says, with the forum root given explicitly
            # because this run is not made from inside the forum.
            includes:
                - {$package}/extension.neon
                - {$package}/crates.neon

            parameters:
                level: 5
                tmpDir: {$this->work}/cache
                paths:
                    - {$this->addon()}
                excludePaths:
                    analyseAndScan:
                        - {$this->addon()}/vendor (?)
                    analyse:
                        - {$this->addon()}/_output (?)
                        - {$this->addon()}/_releases (?)
                        - {$this->addon()}/_data (?)
                xenforo:
                    root: {$this->forum()}
                    generated: {$this->generated}
            NEON;
    }

    /**
     * @return array{totals: array{file_errors: int}, files?: array<string, array{messages: list<array{line: int, message: string}>}>}
     */
    private function analyse(string $neon): array
    {
        $config = $this->work . '/phpstan.neon';
        file_put_contents($config, $neon);

        // Run from the package root, so that PHPStan's launcher loads this package's autoloader
        // and the extension classes resolve - as they do in an add-on that has installed it.
        // stderr is kept apart: PHPStan writes advice there that is not JSON.
        $stderr = $this->work . '/stderr.txt';
        $command = sprintf(
            'cd %s && %s analyse -c %s --no-progress --error-format=json 2>%s',
            escapeshellarg(dirname(__DIR__)),
            escapeshellarg(dirname(__DIR__) . '/vendor/bin/phpstan'),
            escapeshellarg($config),
            escapeshellarg($stderr),
        );
        $output = (string) shell_exec($command);
        $result = json_decode($output, true);

        $this->assertIsArray($result, 'PHPStan did not return JSON: ' . $output . (string) file_get_contents($stderr));

        /** @var array{totals: array{file_errors: int}, files?: array<string, array{messages: list<array{line: int, message: string}>}>} $result */
        return $result;
    }

    private function forum(): string
    {
        return Fixture::forum();
    }

    private function addon(): string
    {
        return Fixture::addon();
    }
}
