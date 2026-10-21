<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Tests\Type;

use Hampel\XenForo\PHPStan\Type\Shortcut;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ShortcutTest extends TestCase
{
    /** @return iterable<string, array{string, string, string|null}> */
    public static function identifiers(): iterable
    {
        // Each row is \XF::stringToClass() with the formatter the shortcut uses, measured on 2.3.
        yield 'repository' => ['XF:User', Shortcut::REPOSITORY, 'XF\Repository\User'];
        yield 'entity' => ['XF:User', Shortcut::ENTITY, 'XF\Entity\User'];
        yield 'finder' => ['XF:User', Shortcut::FINDER, 'XF\Finder\User'];
        yield 'service with a nested name' => ['Acme\Demo:Widget\Create', Shortcut::SERVICE, 'Acme\Demo\Service\Widget\Create'];
        yield 'data' => ['XF:Robot', Shortcut::DATA, 'XF\Data\Robot'];
        yield 'behavior' => ['XF:DevOutputWritable', Shortcut::BEHAVIOR, 'XF\Behavior\DevOutputWritable'];
        yield 'job' => ['Acme\Demo:Rebuild', Shortcut::JOB, 'Acme\Demo\Job\Rebuild'];
        yield 'plugin' => ['XF:Editor', Shortcut::PLUGIN, 'XF\ControllerPlugin\Editor'];
        yield 'plugin under an application type' => ['Acme\Demo:Admin:Tools', Shortcut::PLUGIN, 'Acme\Demo\Admin\ControllerPlugin\Tools'];
        yield 'three parts to a two-part formatter keep the ends' => ['Acme\Demo:Ignored:Widget', Shortcut::REPOSITORY, 'Acme\Demo\Repository\Widget'];
        yield 'a class name is not the short form' => ['XF\Repository\UserRepository', Shortcut::REPOSITORY, null];
        yield 'a class-string with a leading slash either' => ['\XF\Entity\User', Shortcut::ENTITY, null];
    }

    #[DataProvider('identifiers')]
    public function testItExpandsTheIdentifierAsXenForoDoes(string $identifier, string $kind, ?string $class): void
    {
        $this->assertSame($class, Shortcut::classFor($identifier, $kind));
    }
}
