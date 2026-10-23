<?php

namespace XF\Db;

abstract class AbstractAdapter
{
    /**
     * As XenForo documents it: the array claim the stub corrects.
     *
     * @param $query
     * @param array $params
     *
     * @return AbstractStatement
     */
    public function query($query, $params = [])
    {
        return new class () extends AbstractStatement {};
    }

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
