<?php

namespace XF\Db;

abstract class AbstractAdapter
{
    public function quote($data, $type = null)
    {
        return "'" . $data . "'";
    }

    public function fetchAllColumn($query, $params = [], $column = 0)
    {
        return [];
    }

    public function getQueryCount()
    {
        return 0;
    }
}
