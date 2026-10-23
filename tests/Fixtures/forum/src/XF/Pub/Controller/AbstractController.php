<?php

namespace XF\Pub\Controller;

abstract class AbstractController extends \XF\Mvc\Controller
{
    // An action, undeclared as XenForo's are: an extension's `return parent::actionIndex()`.
    public function actionIndex()
    {
        return $this->view();
    }
}
