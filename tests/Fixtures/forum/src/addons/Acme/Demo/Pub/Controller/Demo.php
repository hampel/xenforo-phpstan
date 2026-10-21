<?php

namespace Acme\Demo\Pub\Controller;

use XF\Pub\Controller\AbstractController;

/**
 * Every call here is one PHPStan cannot type from the forum's source alone. The last two are
 * genuinely wrong and must still be reported.
 */
class Demo extends AbstractController
{
    public function actionIndex(): void
    {
        $user = \XF::em()->find('XF:User', 1);
        if ($user !== null) {
            $user->canUseDemo();        // added by this add-on's extension of XF\Entity\User
            $user->fromOther();         // added by another add-on's extension
            $user->isMemberOf(2);       // on the core class
        }

        $this->repository('XF:User')->findDemoUsers()->fetchOne();      // extension registered on a legacy name
        $this->repository('XF:User')->getVisitor()->canUseDemo();       // the renamed core repository
        $this->repository('Acme\Demo:Widget')->countWidgets();          // the add-on's own repository
        $this->service('Acme\Demo:Widget\Create')->setName('x');        // the add-on's own renamed service
        $this->plugin('XF:Editor')->getEditorHtml();                    // a renamed controller plugin
        $this->finder('XF:User')->isValidUser();                        // a renamed finder
        $this->finder('Acme\Demo:Widget')->fetch();                     // an entity with no finder of its own
        \XF::repository('XF:User')->getVisitor();                       // the static shortcut
        $this->app()->em()->create('XF:User')->save();                  // the entity manager
        $this->assertRecordExists('XF:User', 1)->canUseDemo();          // a controller's record assertion
        $this->assertRecordExists('XF:User', 1)->getBehavior('XF:DevOutputWritable')->getOutputPath();

        $this->repository('Acme\Demo:Widget')->noSuchMethod();          // ERROR: not on the repository
        $user?->noSuchMethodEither();                                   // ERROR: on no extension
    }
}
