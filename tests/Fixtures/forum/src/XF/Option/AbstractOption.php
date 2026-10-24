<?php

namespace XF\Option;

use XF\Entity\Option;

// As XenForo declares them: each row helper hands back the templater's form row, undeclared.
abstract class AbstractOption
{
    protected static function getTextboxRow(Option $option, array $htmlParams, $value = null)
    {
        return '<dl>' . $option->option_id . '</dl>';
    }

    protected static function getSelectRow(Option $option, array $htmlParams, array $choices, $value = null)
    {
        return '<dl>' . $option->option_id . '</dl>';
    }

    protected static function getRadioRow(Option $option, array $htmlParams, array $choices, $value = null)
    {
        return '<dl>' . $option->option_id . '</dl>';
    }

    protected static function getCheckboxRow(Option $option, array $htmlParams, array $choices, $value = null)
    {
        return '<dl>' . $option->option_id . '</dl>';
    }

    protected static function getNumberBoxRow(Option $option, array $htmlParams, $value = null)
    {
        return '<dl>' . $option->option_id . '</dl>';
    }
}
