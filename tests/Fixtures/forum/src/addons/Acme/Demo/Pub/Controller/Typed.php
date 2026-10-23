<?php

namespace Acme\Demo\Pub\Controller;

use Acme\Demo\Service\Widget\CreateService;
use XF\AddOn\AddOn;
use XF\ContinuationResult;
use XF\Db\AbstractStatement;
use XF\Db\Schema\Column;
use XF\Entity\Node;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\Error;
use XF\Mvc\Reply\Message;
use XF\Mvc\Reply\Redirect;
use XF\Mvc\Reply\View;
use XF\Phrase;
use XF\Pub\Controller\AbstractController;
use XF\Service\User\MergeService;
use XF\SubTree;
use XF\Tree;

/**
 * Every member stubs/core.stub types, each used where only the stubbed type passes at level
 * max: XenForo declares none of these, so without the stub every line is a mixed being passed
 * where a type is expected. Analysed alone at level max; the lines marked ERROR are what
 * prove the run looked.
 */
class Typed extends AbstractController
{
    public function actionView(): View
    {
        $view = $this->view('Acme\Demo:Index', 'acme_index', ['x' => 1]);
        $this->takesArray($view->getParams());
        $this->takesArray($view->getJsonParams());

        return $view;
    }

    public function actionRedirect(): Redirect
    {
        $redirect = $this->redirect('/', 'done');
        $this->takesString($redirect->getUrl());
        $this->takesString($this->redirectPermanently('/')->getUrl());

        return $redirect;
    }

    public function actionError(): Error
    {
        return $this->error('x', 403);
    }

    public function actionMessage(): Message
    {
        return $this->message('x');
    }

    public function actionThrow(): never
    {
        throw $this->exception($this->error('x'));
    }

    public function actionFinder(): void
    {
        // The fluent methods return static, so the chain keeps the entity the finder was made for.
        $user = $this->finder('XF:User')->where('user_id', 1)->order('username')->with('Option')->limit(1)->fetchOne();
        if ($user !== null) {
            $user->canUseDemo();
        }

        $this->takesList($this->finder('XF:User')->fetch()->keys());

        // Iterating a collection, or the finder itself, yields the entity. getIterator() implements
        // an interface method PHPStan stubs, and for a stubbed class that stub outranks XenForo's
        // own docblock; without the stub restating it, the collection's $member is mixed.
        foreach ($this->finder('XF:User')->fetch() as $member) {
            $member->canUseDemo();
        }
        foreach ($this->finder('XF:User') as $member) {
            $member->canUseDemo();
        }
    }

    public function actionNode(): void
    {
        $nodes = $this->repository('XF:Node')->getFullNodeList();
        $this->takesNodes($nodes);
        $this->takesNodes($this->repository('XF:Node')->filterViewable($nodes));

        $node = $this->assertRecordExists('XF:Node', 1);
        $this->takesCrumbs($node->getBreadcrumbs(false));
        $this->takesArray($node->getNodeListExtras());
        $this->takesArray($this->repository('XF:Node')->mergeNodeListExtras($node->getNodeListExtras(), []));
    }

    public function actionTree(): void
    {
        // The tree is generic over its record: a node here, bound by createNodeTree().
        $tree = $this->repository('XF:Node')->createNodeTree($this->repository('XF:Node')->getFullNodeList());
        $this->takesTree($tree->filter());
        $this->takesNodeOrNull($tree->getData(1));
        $this->takesSubTreeOrNull($tree[1]);
        $this->takesSubTreeOrNull($tree->parent(1));
        $this->takesNodeMap($tree->getDescendants());

        foreach ($tree as $subTree) {
            $this->takesNode($subTree->record);
            $this->takesInt(count($subTree->children()));
        }
        foreach ($tree->children(1) as $subTree) {
            $this->takesNode($subTree->record);
        }
        foreach ($tree->getFlattened() as $row) {
            $this->takesNode($row['record']);
            $this->takesInt($row['depth']);
        }

        // A tree built by hand is bound by what it was given.
        $built = new Tree(['a' => ['parent_id' => 0, 'name' => 'x']]);
        foreach ($built as $subTree) {
            $this->takesString($subTree->record['name']);
        }
    }

    public function actionApp(): void
    {
        $this->takesString($this->app()->router()->buildLink('index'));
        $this->takesPhrase(\XF::phrase('x', ['a' => 1]));

        $db = $this->app()->db();
        $this->takesString($db->quote('x'));
        $this->takesInt($db->getQueryCount());
        $this->takesList($db->fetchAllColumn('SELECT 1'));
        $this->takesStatement($db->query('SELECT ?', 1));           // a scalar, as core passes one

        $this->takesEntityOrNull($this->app()->findByContentType('node', 1));
        $this->takesCollection($this->app()->findByContentType('node', [1, 2]));
        $this->takesAddOnOrNull($this->app()->addOnManager()->getById('Acme/Demo'));

        // The add-on object answers properties by magic: its own keys, then the installed
        // entity's columns. A key it does not have is reported, which a crate would not do.
        $addOn = $this->app()->addOnManager()->getById('Acme/Demo');
        if ($addOn !== null) {
            $this->takesString($addOn->title);
            $this->takesInt($addOn->json_version_id);
            $this->takesStringOrNull($addOn->legacy_addon_id);
            $this->takesAddOnEntityOrNull($addOn->addon);
            $typo = $addOn->titel;                                      // ERROR: a mistyped key
        }
    }

    public function actionEntity(): void
    {
        $user = $this->assertRecordExists('XF:User', 1);
        $this->takesBool($user->save());
    }

    public function actionSchema(): void
    {
        $table = new \XF\Db\Schema\Create();
        $this->takesColumn($table->addColumn('title', 'varchar', 100)->nullable());
    }

    public function actionRequest(): void
    {
        $this->takesArray($this->request()->filter(['a' => 'str', 'b' => 'uint']));
    }

    public function actionInput(): void
    {
        // Request input typed from the filter string, as XF\InputFilterer casts it.
        $this->takesString($this->filter('note', 'str'));
        $this->takesInt($this->filter('id', 'uint'));
        $this->takesBool($this->filter('flag', 'bool'));
        $this->takesNumber($this->filter('amount', 'num'));
        $this->takesStringOrNull($this->filter('maybe', '?str'));
        $this->takesIntArray($this->filter('ids', 'array-uint'));
        $this->takesDateTime($this->filter('when', 'datetime,obj'));
        $this->takesString($this->request()->filter('q', 'string'));

        $input = $this->filter(['username' => 'str', 'is_admin' => 'bool', 'ids' => 'array-uint', 'extra' => ['x' => 'int']]);
        $this->takesString($input['username']);
        $this->takesBool($input['is_admin']);
        $this->takesIntArray($input['ids']);
        $this->takesInt($input['extra']['x']);

        $this->takesInt($this->filter('note', 'str'));                 // ERROR: str is a string

        $this->takesInt($this->view()->getParam('x'));                 // ERROR: a single parameter is mixed
    }

    public function actionReplies(): void
    {
        // Every undeclared action returns a reply, through parent:: as an extension calls it.
        $this->takesReply(parent::actionIndex());
        $this->takesReply($this->actionIndex());
        $this->takesView($this->actionView());                           // a declared type is kept
    }

    public function actionCore(): void
    {
        // Returns XenForo leaves undeclared on members an extension returns through parent::.
        $user = $this->assertRecordExists('XF:User', 1);
        $this->takesBool($user->canFollowUser($user));
        $this->takesBool($user->canViewFullProfile($error));
        $this->takesBool($user->canViewBasicProfile($error));
        $this->takesBool($user->hasAdminPermission('user'));
        $this->takesEntity($user->getRelationOrDefault('Option'));
        $this->takesErrors($user->getErrors());

        $merger = $this->app()->service(MergeService::class);
        $this->takesContinuation($merger->setSource($user)->setTarget($user)->merge());
        $this->takesContinuation(ContinuationResult::completed());

        // The trait, declared in the stub, types validate() on any service that uses it.
        $creator = $this->app()->service(CreateService::class);
        $this->takesBool($creator->validate($errors));
        $this->takesErrors($errors);

        $structure = new Structure();
        $structure->relations['Demo'] = ['entity' => 'Acme\Demo:Thing'];
        $this->takesArray($structure->relations);

        $db = $this->app()->db();
        $this->takesRowOrFalse($db->fetchRow('SELECT 1'));
        $this->takesRows($db->fetchAll('SELECT 1'));
        $this->takesPairs($db->fetchPairs('SELECT 1'));
    }

    /** @param array<string, mixed> $value */
    private function takesArray(array $value): void
    {
    }

    /** @param list<mixed> $value */
    private function takesList(array $value): void
    {
    }

    private function takesString(string $value): void
    {
    }

    private function takesInt(int $value): void
    {
    }

    private function takesBool(bool $value): void
    {
    }

    private function takesPhrase(Phrase $value): void
    {
    }

    private function takesColumn(Column $value): void
    {
    }

    private function takesEntityOrNull(?Entity $value): void
    {
    }

    private function takesAddOnOrNull(?AddOn $value): void
    {
    }

    private function takesAddOnEntityOrNull(?\XF\Entity\AddOn $value): void
    {
    }

    private function takesStringOrNull(?string $value): void
    {
    }

    private function takesNumber(float|int $value): void
    {
    }

    /** @param array<int> $value */
    private function takesIntArray(array $value): void
    {
    }

    private function takesDateTime(\DateTime $value): void
    {
    }

    private function takesEntity(Entity $value): void
    {
    }

    private function takesReply(AbstractReply $value): void
    {
    }

    private function takesView(View $value): void
    {
    }

    /** @param array<string|int, Phrase|string> $value */
    private function takesErrors(array $value): void
    {
    }

    private function takesContinuation(ContinuationResult $value): void
    {
    }

    /** @param array<string, mixed>|false $value */
    private function takesRowOrFalse(array|false $value): void
    {
    }

    /** @param list<array<string, mixed>> $value */
    private function takesRows(array $value): void
    {
    }

    /** @param array<int|string, mixed> $value */
    private function takesPairs(array $value): void
    {
    }

    private function takesStatement(AbstractStatement $value): void
    {
    }

    /** @param AbstractCollection<Entity> $value */
    private function takesCollection(AbstractCollection $value): void
    {
    }

    /** @param AbstractCollection<Node> $value */
    private function takesNodes(AbstractCollection $value): void
    {
    }

    /** @param list<array{value: string, href: string, node_id: int}> $value */
    private function takesCrumbs(array $value): void
    {
    }

    private function takesNode(Node $value): void
    {
    }

    private function takesNodeOrNull(?Node $value): void
    {
    }

    /** @param array<int|string, Node> $value */
    private function takesNodeMap(array $value): void
    {
    }

    /** @param Tree<Node> $value */
    private function takesTree(Tree $value): void
    {
    }

    /** @param SubTree<Node>|null $value */
    private function takesSubTreeOrNull(?SubTree $value): void
    {
    }
}
