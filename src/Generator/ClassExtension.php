<?php

declare(strict_types=1);

namespace Hampel\XenForo\PHPStan\Generator;

/**
 * One line of an add-on's _data/class_extensions.xml.
 */
final class ClassExtension
{
    public function __construct(
        public readonly string $fromClass,
        public readonly string $toClass,
    ) {
    }

    /**
     * The parent XenForo declares for the extending class in _output/extension_hint.php:
     * XFCP_<ShortName>, in the extending class's own namespace.
     */
    public function hintClass(): string
    {
        $parts = explode('\\', $this->toClass);
        $short = array_pop($parts);

        return implode('\\', [...$parts, 'XFCP_' . $short]);
    }

    /**
     * Every extension declared in a set of class_extensions.xml files, in file order.
     *
     * @param  list<string>  $files
     * @return list<self>
     */
    public static function fromXmlFiles(array $files): array
    {
        $extensions = [];
        foreach ($files as $file) {
            $xml = @simplexml_load_file($file);
            if ($xml === false) {
                throw new \RuntimeException(sprintf('%s is not well-formed XML.', $file));
            }

            foreach ($xml->extension as $extension) {
                $extensions[] = new self(
                    ltrim((string) $extension['from_class'], '\\'),
                    ltrim((string) $extension['to_class'], '\\'),
                );
            }
        }

        return $extensions;
    }
}
