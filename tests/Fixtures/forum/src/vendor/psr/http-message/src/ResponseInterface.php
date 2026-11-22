<?php

namespace Psr\Http\Message;

interface ResponseInterface
{
    /**
     * @return int
     */
    public function getStatusCode();
}
