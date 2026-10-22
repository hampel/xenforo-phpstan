<?php

namespace XF\Db\Schema;

class Column
{
    public function nullable(bool $nullable = true): static
    {
        return $this;
    }
}
