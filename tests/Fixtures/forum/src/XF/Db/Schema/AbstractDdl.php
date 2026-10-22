<?php

namespace XF\Db\Schema;

// The real class documents the index name as `@param null`, which is the defect the stub corrects.
abstract class AbstractDdl
{
    public function addColumn($columnName, $type = null, $length = null)
    {
        return new Column();
    }

    /**
     * @param $column
     * @param null $indexName
     *
     * @return Index
     */
    public function addUniqueKey($column, $indexName = null)
    {
        return new Index();
    }

    /**
     * @param $column
     * @param null $indexName
     *
     * @return Index
     */
    public function addKey($column, $indexName = null)
    {
        return new Index();
    }

    /**
     * @param $column
     * @param null $indexName
     *
     * @return Index
     */
    public function addFullTextKey($column, $indexName = null)
    {
        return new Index();
    }
}
