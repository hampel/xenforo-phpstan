<?php

namespace XF\Mvc\Entity;

class Finder
{
    public function fetchOne(): ?Entity
    {
        return null;
    }

    /** @return list<Entity> */
    public function fetch(): array
    {
        return [];
    }
}
