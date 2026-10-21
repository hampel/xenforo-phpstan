<?php

namespace XF\Mvc\Entity;

abstract class Repository
{
    /**
     * @template T of Finder
     *
     * @param class-string<T> $identifier
     *
     * @return T
     */
    public function finder($identifier)
    {
        return \XF::em()->getFinder($identifier);
    }

    /**
     * @template T of Repository
     *
     * @param class-string<T> $identifier
     *
     * @return T
     */
    public function repository($identifier)
    {
        return \XF::em()->getRepository($identifier);
    }
}
