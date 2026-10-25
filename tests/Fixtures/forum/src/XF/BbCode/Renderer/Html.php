<?php

namespace XF\BbCode\Renderer;

// As XenForo declares it: filterString() undeclared, returning the filtered string.
class Html extends AbstractRenderer
{
    public function getCustomTagConfig(array $tag)
    {
        return [];
    }

    public function renderTag(array $tag, array $options)
    {
        return $this->renderUnparsedTag($tag, $options);
    }

    public function renderString($string, array $options)
    {
        return $this->filterString($string, $options);
    }

    public function filterString($string, array $options)
    {
        return htmlspecialchars($string);
    }
}
