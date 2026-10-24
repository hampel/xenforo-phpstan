<?php

namespace XF\Db\Schema;

// The fluent setters return $this, undeclared, as in XenForo; the stub declares them static.
class Column
{
    public function renameTo($renameTo)
    {
        return $this;
    }

    public function type($type, $length = null)
    {
        return $this;
    }

    public function length($length)
    {
        return $this;
    }

    public function values($values)
    {
        return $this;
    }

    public function addValues($values)
    {
        return $this;
    }

    public function removeValues($values)
    {
        return $this;
    }

    public function unsigned($unsigned = null)
    {
        return $this;
    }

    public function nullable($nullable = null)
    {
        return $this;
    }

    public function setDefault($default)
    {
        return $this;
    }

    public function autoIncrement($autoIncrement = null, $setAsPrimary = true)
    {
        return $this;
    }

    public function primaryKey()
    {
        return $this;
    }

    public function comment($comment)
    {
        return $this;
    }

    public function after($after)
    {
        return $this;
    }

    public function resetDefinition()
    {
        return $this;
    }
}
