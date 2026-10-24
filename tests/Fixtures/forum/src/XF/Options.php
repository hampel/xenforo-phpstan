<?php

namespace XF;

/**
 * @extends \ArrayObject<string, mixed>
 */
#[\AllowDynamicProperties]
class Options extends \ArrayObject
{
    /**
     * @param array<string, mixed> $array
     */
    public function __construct(array $array = [])
    {
        parent::__construct($array, \ArrayObject::ARRAY_AS_PROPS);
    }
}
