<?php

namespace XF\AddOn;

// Answers $addOn->key through __get(): nine keys of its own, then the installed entity's
// columns, then addon.json. Undeclared, so PHPStan sees only the magic.
class AddOn
{
    public function getInstalledAddOn()
    {
        return null;
    }

    public function getJson()
    {
        return [];
    }

    public function __get($key)
    {
        return null;
    }
}
