<?php

namespace Acme\Demo\Pub\Controller;

use XF\Db\Schema\Column;
use XF\Entity\Node;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Reply\Error;
use XF\Mvc\Reply\Message;
use XF\Mvc\Reply\Redirect;
use XF\Mvc\Reply\View;
use XF\Phrase;
use XF\Pub\Controller\AbstractController;
use XF\SubTree;
use XF\Tree;

/**
 * Every member stubs/core.stub types, each used where only the stubbed type passes at level
 * max: XenForo declares none of these, so without the stub every line is a mixed being passed
 * where a type is expected. Analysed alone at level max; the one line marked ERROR is what
 * proves the run looked.
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

        $this->takesEntityOrNull($this->app()->findByContentType('node', 1));
        $this->takesCollection($this->app()->findByContentType('node', [1, 2]));
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

        $this->takesInt($this->view()->getParam('x'));                 // ERROR: a single parameter is mixed
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
