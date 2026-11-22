<?php

namespace Acme\Demo\SubContainer;

use Acme\Demo\Api\Client;
use XF\SubContainer\AbstractSubContainer;

/**
 * A sub-container as an add-on writes one: entries registered as closures, read back through
 * getters. The add-on's phpstan.neon declares what `client` and `retries` hold; `endpoint` is
 * left undeclared.
 */
class Api extends AbstractSubContainer
{
    public function initialize()
    {
        $container = $this->container;

        $container['client'] = function ($c) {
            return new Client();
        };
        $container['retries'] = 3;
        $container['endpoint'] = 'https://example.com';
    }

    public function client(): Client
    {
        return $this->container['client'];
    }

    public function wrong(): void
    {
        // Each is reported only because the read is typed: mixed is silent at level 5.
        $this->takesInt($this->container['client']);     // ERROR: the declared client
        $this->takesString($this->container['retries']); // ERROR: the declared int
        $this->takesString($this['retries']);            // ERROR: the sub-container read as an array

        // Undeclared, so mixed, and silent below level 9.
        $this->takesInt($this->container['endpoint']);

        // The parent is the application's container, where `client` is no key of this
        // add-on's; and `acme.api` there is this sub-container.
        $this->takesInt($this->parent['client']);
        $this->takesInt($this->parent['acme.api']);      // ERROR: the application's key
    }

    private function takesInt(int $value): void
    {
    }

    private function takesString(string $value): void
    {
    }
}
