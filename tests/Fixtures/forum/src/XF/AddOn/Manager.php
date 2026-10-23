<?php

namespace XF\AddOn;

class Manager
{
    public function getById($addOnId)
    {
        return $addOnId === '' ? null : new AddOn();
    }
}
