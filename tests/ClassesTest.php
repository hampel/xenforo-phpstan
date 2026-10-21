<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Tests;

use Hampel\XenForo\PHPStan\Classes;
use Hampel\XenForo\PHPStan\Generator\Generator;
use PHPUnit\Framework\TestCase;

final class ClassesTest extends TestCase
{
    private string $out;

    protected function setUp(): void
    {
        [$this->out] = Fixture::generate();
    }

    protected function tearDown(): void
    {
        Fixture::remove($this->out);
    }

    public function testItAnswersFromTheGeneratedMap(): void
    {
        $classes = new Classes($this->out . '/' . Generator::CLASSES, Fixture::forum());

        $this->assertSame(2031370, $classes->versionId());
        $this->assertSame('XF\Repository\UserRepository', $classes->resolveAlias('\XF\Repository\User'));
        $this->assertSame('XF\Repository\UserRepository', $classes->resolveAlias('XF\Repository\UserRepository'));
        $this->assertSame(['Acme\Demo\XF\Entity\User', 'Acme\Other\XF\Entity\User'], $classes->extensionsOf('XF\Entity\User'));
        $this->assertSame([], $classes->extensionsOf('XF\Entity\Thread'));
    }

    public function testAMissingFileSaysWhatToRun(): void
    {
        $classes = new Classes($this->out . '/missing.php', Fixture::forum());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Run `vendor/bin/xenforo-phpstan generate` against the forum at ' . Fixture::forum());

        $classes->aliases();
    }

    public function testAFileGeneratedForAnotherVersionOfTheForumIsRefused(): void
    {
        $file = $this->out . '/' . Generator::CLASSES;
        file_put_contents($file, str_replace("'versionId' => 2031370", "'versionId' => 2031270", (string) file_get_contents($file)));
        $classes = new Classes($file, Fixture::forum());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('was generated for XenForo 2031270, but the forum at ' . Fixture::forum() . ' is 2031370');

        $classes->aliases();
    }

    public function testTheForumVersionIsReadFromXfPhpWithoutLoadingIt(): void
    {
        $this->assertSame(2031370, Classes::forumVersionId(Fixture::forum()));
        $this->assertNull(Classes::forumVersionId($this->out));
    }
}
