<?php

namespace XF;

class ContinuationResult
{
    public function __construct(protected bool $completed)
    {
    }

    public static function completed()
    {
        return new self(true);
    }
}
