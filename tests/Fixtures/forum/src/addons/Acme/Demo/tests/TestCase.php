<?php

namespace Tests;

// The add-on's own base test case, which a vendored copy of the same name must not shadow.
abstract class TestCase
{
    protected function helper(): int
    {
        return 1;
    }
}
