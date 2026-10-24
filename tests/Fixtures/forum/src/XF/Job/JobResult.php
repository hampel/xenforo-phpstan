<?php

namespace XF\Job;

class JobResult
{
    public const RESULT_COMPLETED = 1;

    public const RESULT_FAILED = 4;

    public $result;

    // As XenForo declares it: one key by magic, a warning for any other.
    public function __get(string $name)
    {
        switch ($name) {
            case 'completed':
                return $this->result === self::RESULT_COMPLETED || $this->result === self::RESULT_FAILED;
        }

        trigger_error('Undefined property: ' . self::class . '::' . $name, E_USER_WARNING);

        return null;
    }
}
