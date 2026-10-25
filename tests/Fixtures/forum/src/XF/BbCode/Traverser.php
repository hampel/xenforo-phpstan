<?php

namespace XF\BbCode;

// As XenForo declares it: the render methods undeclared, each a string concatenation.
abstract class Traverser
{
    abstract public function renderTag(array $tag, array $options);

    abstract public function renderString($string, array $options);

    public function renderSubTree(array $tree, array $options)
    {
        $output = '';
        foreach ($tree as $element) {
            $output .= is_array($element)
                ? $this->renderTag($element, $options)
                : $this->renderString($element, $options);
        }

        return $output;
    }

    public function renderSubTreePlain(array $tree)
    {
        $output = '';
        foreach ($tree as $element) {
            $output .= is_array($element)
                ? $element['original'][0] . $this->renderSubTreePlain($element['children']) . $element['original'][1]
                : $element;
        }

        return $output;
    }

    public function renderUnparsedTag(array $tag, array $options)
    {
        return $this->renderString($tag['original'][0], $options)
            . $this->renderSubTree($tag['children'], $options)
            . $this->renderString($tag['original'][1], $options);
    }
}
