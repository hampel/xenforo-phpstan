<?php

namespace XF\ControllerPlugin;

// Renamed in 2.3; XF\ControllerPlugin\Editor is the runtime alias.
class EditorPlugin extends AbstractPlugin
{
    public function getEditorHtml(): string
    {
        return '';
    }
}
