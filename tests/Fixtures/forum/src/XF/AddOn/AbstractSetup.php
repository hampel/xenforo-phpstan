<?php

namespace XF\AddOn;

abstract class AbstractSetup
{
    /**
     * @param array $stepParams
     *
     * @return null|StepResult
     */
    abstract public function install(array $stepParams = []);

    /**
     * @param array $stepParams
     *
     * @return null|StepResult
     */
    abstract public function upgrade(array $stepParams = []);

    /**
     * @param array $stepParams
     *
     * @return null|StepResult
     */
    abstract public function uninstall(array $stepParams = []);
}
