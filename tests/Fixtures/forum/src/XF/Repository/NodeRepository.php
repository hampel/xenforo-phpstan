<?php

namespace XF\Repository;

// Renamed in 2.3; XF\Repository\Node is the runtime alias.
class NodeRepository extends \XF\Mvc\Entity\Repository
{
    public function getFullNodeList(?\XF\Entity\Node $withinNode = null, $with = null)
    {
        return new \XF\Mvc\Entity\ArrayCollection();
    }

    public function filterViewable(\XF\Mvc\Entity\AbstractCollection $nodes)
    {
        return $nodes;
    }

    public function createNodeTree($nodes, $rootId = 0)
    {
        return new \XF\Tree($nodes, 'parent_node_id', $rootId);
    }

    public function mergeNodeListExtras(array $extras, array $childExtras)
    {
        return array_merge(['childCount' => count($childExtras)], $extras);
    }
}
