<?php

namespace XF\Api\Result;

use XF\Entity\ResultInterface;

class ArrayResult implements ResultInterface
{
    public function __construct(array $result)
    {
    }
}
