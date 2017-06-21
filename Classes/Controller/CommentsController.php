<?php
namespace WebExcess\Comments\Controller;

/*
 * This file is part of the WebExcess.Comments package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\FluidAdaptor\View\TemplateView;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use WebExcess\Comments\Domain\Model\Comment;

class CommentsController extends ActionController
{

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @Flow\InjectConfiguration()
     * @var array
     */
    protected $settings;

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign('comment', new Comment());
    }

    /**
     * @param Comment $comment
     * @return void
     */
    public function createAction(Comment $comment)
    {
        $documentNode = $this->request->getInternalArgument('__documentNode');
        $q = new FlowQuery(array($documentNode));

        /** @var NodeInterface $commentsCollection */
        $commentsCollection = $q->find('[instanceof WebExcess.Comments:CommentsList]')->children('comments')->context(['workspaceName' => 'live'])->get(0);
        if ($comment->getReference() != '') {
            $commentsCollection = $q->find('#' . $comment->getReference())->children('comments')->context(['workspaceName' => 'live'])->get(0);
        }

        if ($commentsCollection !== null) {
            $propertyNames = $this->reflectionService->getClassPropertyNames('WebExcess\Comments\Domain\Model\Comment');
            $commentNodeType = $this->nodeTypeManager->getNodeType('WebExcess.Comments:Comment');

            $newCommentNode = $commentsCollection->createNode(uniqid('comment-'), $commentNodeType);
            $newCommentNode->setHidden(false);
            $newCommentNode->setProperty('publishingDate', new \DateTime());

            foreach ($propertyNames as $propertyName) {
                $methodName = $propertyName == 'notify' ? 'is' . ucfirst($propertyName) : 'get' . ucfirst($propertyName);
                if (method_exists($comment, $methodName)) {
                    $method = $methodName;
                    if (array_key_exists($propertyName, $commentNodeType->getProperties())) {
                        $newCommentNode->setProperty($propertyName, $comment->$method());
                    }
                }
            }

            $this->persistenceManager->persistAll();
            $this->redirect('success');
        }
    }

    /**
     * @return void
     */
    public function successAction()
    {
    }

    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);

        /** @var TemplateView $view */
        $partialRootPaths = $view->getTemplatePaths()->getPartialRootPaths();
        $partialRootPaths[] = 'resource://WebExcess.Comments/Private/Partials/FormElements/' . $this->settings['form']['preset'] . '/';
        $view->getTemplatePaths()->setPartialRootPaths($partialRootPaths);
    }
}
