<?php

namespace Acme\Demo\Pub\Controller;

use XF\Pub\Controller\AbstractController;

/**
 * Every call here is one PHPStan cannot type from the forum's source alone. The ones marked
 * ERROR are genuinely wrong and must still be reported, each with the type the comment names.
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

        // Where the identifier cannot be typed, PHPStan's own answer - the base type - stands.
        $this->repository('Acme\Demo:Nope')->countWidgets();            // ERROR: no such repository
        $this->repository($this->identifier())->countWidgets();         // ERROR: not a constant string

        // Two identifiers give a union; a method neither has is reported against both names.
        $either = random_int(0, 1) === 1 ? 'XF:User' : 'Acme\Demo:Widget';
        $this->repository($either)->onNeither();                        // ERROR: names the union
    }

    public function actionSchema(): void
    {
        // XenForo's own PHPDoc says the index name is null; every Setup.php passes a string.
        $table = new \XF\Db\Schema\Create();
        $table->addUniqueKey(['user_id', 'content_id'], 'user_content');
        $table->addKey('content_id', 'content');
        $table->addFullTextKey('title', 'title');
    }

    private function identifier(): string
    {
        return (string) getenv('DEMO_REPOSITORY');
    }
}
