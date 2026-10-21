<?php

namespace XF\Behavior;

class DevOutputWritable extends \XF\Mvc\Entity\Behavior
{
    public function getOutputPath(): string
    {
        return '';
    }
}
