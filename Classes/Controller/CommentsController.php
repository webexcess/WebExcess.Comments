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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\FluidAdaptor\View\TemplateView;
use WebExcess\Comments\Domain\Model\CommentInterface;
use Neos\Neos\Exception;
use WebExcess\Comments\Domain\Service\CommentService;
use WebExcess\Comments\Service\NodeUriBuilder;
use Neos\Error\Messages\Message;

class CommentsController extends ActionController
{
    /**
     * @Flow\InjectConfiguration()
     * @var array
     */
    protected $settings;


    /**
     * @Flow\Inject()
     * @var NodeUriBuilder
     */
    protected $nodeUriBuilder;

    /**
     * @Flow\Inject
     * @var CommentService
     */
    protected $commentService;

    /**
     * @return void
     * @throws Exception
     */
    public function indexAction()
    {
        $comment = $this->objectManager->get(CommentInterface::class);
        $isLoggedIn = $comment->loadAccountDataIfAuthenticated();

        $allowCommenting = false;
        if ($isLoggedIn === true && $this->settings['allowCommenting']['account'] === true) {
            $allowCommenting = true;
        }
        if ($isLoggedIn === false && $this->settings['allowCommenting']['guest'] === true) {
            $allowCommenting = true;
        }

        $reCaptcha = false;
        if ($this->settings['reCaptcha']['enabled']) {
            if (!class_exists('\ReCaptcha\ReCaptcha')) {
                throw new Exception('The Class "\ReCaptcha\ReCaptcha" does not exist!');
            }

            $reCaptcha = $this->settings['reCaptcha']['websiteKey'];
        }

        $this->view->assignMultiple(array(
            'comment' => $comment,
            'isLoggedIn' => $isLoggedIn,
            'allowCommenting' => $allowCommenting,
            'reCaptcha' => $reCaptcha,
        ));
    }

    /**
     * @param CommentInterface $comment
     * @return void
     * @throws Exception
     */
    public function createAction(CommentInterface $comment)
    {
        /** @var NodeInterface $documentNode */
        $documentNode = $this->request->getInternalArgument('__documentNode');

        $this->commentService->addComment($comment, $documentNode);
        $this->flashMessageContainer->addMessage(new Message('Comment successfully added', 1499693207));

        $this->redirectToUri($this->nodeUriBuilder->getUriToNode($documentNode));
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
