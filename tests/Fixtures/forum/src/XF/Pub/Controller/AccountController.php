<?php

namespace XF\Pub\Controller;

use XF\Entity\User;

// As XenForo declares them: the form action built, filled and returned, with no return type.
class AccountController extends AbstractController
{
    protected function accountDetailsSaveProcess(User $visitor)
    {
        $form = $this->formAction();

        return $form;
    }

    protected function preferencesSaveProcess(User $visitor)
    {
        $form = $this->formAction();

        return $form;
    }
}
