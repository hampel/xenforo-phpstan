<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Tests\Generator;

use Hampel\XenForo\PHPStan\Generator\Generator;
use Hampel\XenForo\PHPStan\Tests\Fixture;
use PHPUnit\Framework\TestCase;

final class GeneratorTest extends TestCase
{
    private string $out;

    protected function tearDown(): void
    {
        if (isset($this->out)) {
            Fixture::remove($this->out);
        }
    }

    public function testItWritesTheThreeFilesExtensionNeonReads(): void
    {
        [$this->out, $summary] = Fixture::generate();

        $this->assertFileExists($this->out . '/' . Generator::ALIASES_STUB);
        $this->assertFileExists($this->out . '/' . Generator::EXTENSIONS_STUB);
        $this->assertFileExists($this->out . '/' . Generator::CLASSES);
        $this->assertSame(2031370, $summary->versionId);
    }

    public function testTheAliasStubDeclaresEachLegacyNameAsASubclassOfTheRenamedClass(): void
    {
        [$this->out, $summary] = Fixture::generate();
        $stub = (string) file_get_contents($this->out . '/' . Generator::ALIASES_STUB);

        $this->assertStringContainsString('namespace XF\Repository' . "\n" . '{' . "\n" . "\t" . 'class User extends \XF\Repository\UserRepository {}' . "\n" . '}', $stub);
        $this->assertStringContainsString("\t" . 'class User extends \XF\Finder\UserFinder {}', $stub);
        $this->assertStringContainsString("\t" . 'class Editor extends \XF\ControllerPlugin\EditorPlugin {}', $stub);

        // An add-on's own renamed class is aliased the same way as core.
        $this->assertStringContainsString('namespace Acme\Demo\Service\Widget' . "\n" . '{' . "\n" . "\t" . 'class Create extends \Acme\Demo\Service\Widget\CreateService {}', $stub);

        // An abstract class is declared abstract...
        $this->assertStringContainsString("\t" . 'abstract class AbstractConfirmation extends \XF\Service\User\AbstractConfirmationService {}', $stub);

        // ...and a name that strips to a reserved word is skipped, not emitted as a parse error.
        $this->assertStringNotContainsString('class Abstract ', $stub);
        $this->assertSame(['XF\ControllerPlugin\Abstract', 'XF\Service\Abstract'], $summary->skippedAliases);

        // The header's count is the count of declarations in the file, not of discoveries.
        $this->assertSame($summary->aliases, preg_match_all('/^\t(?:abstract )?class \w+ extends/m', $stub));
        $this->assertStringContainsString(sprintf('%d aliases, 2 skipped', $summary->aliases), $stub);
        $this->assertSame(1, $summary->abstractAliases);
    }

    public function testTheExtensionStubDeclaresAnXfcpParentForEveryExtensionInTheInstall(): void
    {
        [$this->out, $summary] = Fixture::generate();
        $stub = (string) file_get_contents($this->out . '/' . Generator::EXTENSIONS_STUB);

        // Named as XenForo's own _output/extension_hint.php names them, including for the
        // add-on that ships without one.
        $this->assertStringContainsString('namespace Acme\Demo\XF\Entity' . "\n" . '{' . "\n" . "\t" . 'class XFCP_User extends \XF\Entity\User {}' . "\n" . '}', $stub);
        $this->assertStringContainsString('namespace Acme\Demo\XF\Repository' . "\n" . '{' . "\n" . "\t" . 'class XFCP_User extends \XF\Repository\User {}' . "\n" . '}', $stub);
        $this->assertStringContainsString('namespace Acme\Other\XF\Entity' . "\n" . '{' . "\n" . "\t" . 'class XFCP_User extends \XF\Entity\User {}' . "\n" . '}', $stub);
        $this->assertSame(3, $summary->extensions);
    }

    public function testTheClassMapKeysExtensionsByTheClassXenForoResolvesTheRegisteredNameTo(): void
    {
        [$this->out, $summary] = Fixture::generate();
        $data = require $this->out . '/' . Generator::CLASSES;

        $this->assertIsArray($data);
        $this->assertSame(['versionId' => 2031370, 'root' => Fixture::forum()], $data['xenforo']);

        // Every alias the forum reports, declarable or not: the shortcut extension resolves
        // through this, and the stub's skip list is a property of PHP, not of XenForo.
        $this->assertSame([
            'Acme\Demo\Service\Widget\Create' => 'Acme\Demo\Service\Widget\CreateService',
            'XF\ControllerPlugin\Abstract' => 'XF\ControllerPlugin\AbstractPlugin',
            'XF\ControllerPlugin\Editor' => 'XF\ControllerPlugin\EditorPlugin',
            'XF\Finder\User' => 'XF\Finder\UserFinder',
            'XF\Repository\User' => 'XF\Repository\UserRepository',
            'XF\Service\Abstract' => 'XF\Service\AbstractService',
            'XF\Service\User\AbstractConfirmation' => 'XF\Service\User\AbstractConfirmationService',
        ], $data['aliases']);

        $this->assertSame(2, $data['format']);
        $this->assertSame([
            'XF\Entity\User' => [
                ['class' => 'Acme\Demo\XF\Entity\User', 'addon' => 'Acme/Demo'],
                ['class' => 'Acme\Other\XF\Entity\User', 'addon' => 'Acme/Other'],
            ],
            'XF\Repository\UserRepository' => [
                ['class' => 'Acme\Demo\XF\Repository\User', 'addon' => 'Acme/Demo'],
            ],
        ], $data['extensions']);
        $this->assertSame(1, $summary->legacyExtensions);
        $this->assertSame(2, $summary->extendedClasses);
    }

    public function testItRefusesADirectoryThatIsNotAForum(): void
    {
        $this->out = Fixture::temp('not-a-forum');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('is not a XenForo root');

        (new Generator($this->out))->generate($this->out . '/out');
    }
}
