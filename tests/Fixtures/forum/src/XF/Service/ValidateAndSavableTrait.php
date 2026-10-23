<?php

namespace XF\Service;

trait ValidateAndSavableTrait
{
    public function validate(&$errors = [])
    {
        $errors = [];

        return true;
    }

    public function save()
    {
        return $this->_save();
    }
}
