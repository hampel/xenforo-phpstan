<?php

namespace Acme\Other\Pub\Controller;

use XF\Pub\Controller\AbstractController;

/**
 * The same shortcuts from an add-on that does not extend the repository: the type is the core
 * class, with the other add-on's methods still reachable through it.
 */
class Other extends AbstractController
{
    public function actionIndex(): void
    {
        $user = \XF::em()->find('XF:User', 1);
        if ($user !== null) {
            $user->fromOther();                                         // this add-on's extension
            $user->canUseDemo();                                        // another add-on's extension
            $user->isMemberOf(2);                                       // the core signature
            $user->inactiveMethod();                                    // ERROR: registered inactive, never applied
        }

        // An extension of a class the forum does not have: its parent is the fake base XenForo
        // would substitute, so the chain resolves.
        $this->finder(\Acme\Other\Gallery\Finder\MediaItemFinder::class)->withAlbum()->where('album_id', 1);

        $this->repository('XF:User')->findDemoUsers();                  // another add-on's extension
        $this->repository('XF:User')->nope();                           // ERROR: names the core class
        $this->repository(\XF\Repository\UserRepository::class)->nopeClass(); // ERROR: a class name, PHPStan's own answer
    }
}
