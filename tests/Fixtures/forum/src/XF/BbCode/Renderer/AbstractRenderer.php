<?php

namespace XF\BbCode\Renderer;

use XF\BbCode\Traverser;

abstract class AbstractRenderer extends Traverser
{
    abstract public function getCustomTagConfig(array $tag);
}
