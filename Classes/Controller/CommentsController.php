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
use Neos\Flow\Security\Context;
use Neos\Neos\Domain\Service\UserService;
use Neos\FluidAdaptor\View\TemplateView;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface;
use Neos\Party\Domain\Model\ElectronicAddress;
use WebExcess\Comments\Domain\Model\Comment;
use Neos\Neos\Exception;
use Neos\Error\Messages\Message;

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
     * @var Context
     * @Flow\Inject
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\Inject
     * @var ContentDimensionPresetSourceInterface
     */
    protected $contentDimensionPresetSource;

    /**
     * @return void
     */
    public function indexAction()
    {
        $comment = new Comment();
        $isLoggedIn = $this->setAccountDataIfAuthenticated($comment);

        $allowCommenting = false;
        if ($isLoggedIn === true && $this->settings['allowCommenting']['account'] === true) {
            $allowCommenting = true;
        }
        if ($isLoggedIn === false && $this->settings['allowCommenting']['guest'] === true) {
            $allowCommenting = true;
        }

        $this->view->assignMultiple(array(
            'comment' => $comment,
            'isLoggedIn' => $isLoggedIn,
            'allowCommenting' => $allowCommenting
        ));
    }

    /**
     * @param Comment $comment
     * @return void
     * @throws Exception
     */
    public function createAction(Comment $comment)
    {
        /** @var NodeInterface $documentNode */
        $documentNode = $this->request->getInternalArgument('__documentNode');
        $q = new FlowQuery(array($documentNode));

        $dimensions = array();
        $targetDimension = array();
        if ($this->settings['writeToDefaultDimension'] === true) {
            foreach ($this->contentDimensionPresetSource->getAllPresets() as $dimensionName => $dimensionConfiguration) {
                $dimensions[$dimensionName] = $this->contentDimensionPresetSource->getDefaultPreset($dimensionName)['values'];
                $targetDimension[$dimensionName] = $this->contentDimensionPresetSource->getDefaultPreset($dimensionName)['values'][0];
            }
        } else {
            $dimensions = $documentNode->getContext()->getDimensions();
            $targetDimension = $documentNode->getContext()->getTargetDimensions();
        }

        /** @var NodeInterface $commentsCollection */
        $commentsCollection = $q->find('[instanceof WebExcess.Comments:CommentsList]')->children('comments')->context(['workspaceName' => 'live', 'dimensions' => $dimensions, 'targetDimensions' => $targetDimension])->get(0);
        if ($comment->getReference() != '') {
            $commentsCollection = $q->find('#' . $comment->getReference())->children('comments')->context(['workspaceName' => 'live', 'dimensions' => $dimensions, 'targetDimensions' => $targetDimension])->get(0);
        }

        $this->setAccountDataIfAuthenticated($comment);

        if ($commentsCollection !== null) {
            $propertyNames = $this->reflectionService->getClassPropertyNames('WebExcess\Comments\Domain\Model\Comment');
            $commentNodeType = $this->nodeTypeManager->getNodeType('WebExcess.Comments:Comment');

            $newCommentNode = $commentsCollection->createNode(uniqid('comment-'), $commentNodeType);
            $newCommentNode->setHidden(!$this->settings['publishCommentsLive']);
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
            $this->flashMessageContainer->addMessage(new Message('Comment successfully added', 1499693207));
            $this->redirect('index');
        } else {
            throw new Exception('No "comments" ContentCollection found');
        }
    }

    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);

        /** @var TemplateView $view */
        $partialRootPaths = $view->getTemplatePaths()->getPartialRootPaths();
        $partialRootPaths[] = 'resource://WebExcess.Comments/Private/Partials/FormElements/' . $this->settings['form']['preset'] . '/';
        $view->getTemplatePaths()->setPartialRootPaths($partialRootPaths);
    }

    /**
     * @param Comment $comment
     * @return bool
     * @throws Exception
     */
    private function setAccountDataIfAuthenticated(Comment &$comment) {
        $isLoggedIn = false;
        $authenticationTokens = $this->securityContext->getAuthenticationTokens();
        if (!empty($authenticationTokens)) {
            $account = $this->securityContext->getAccount();
            if ($account !== null) {
                foreach ($authenticationTokens as $authenticationProviderName => $obj) {
                    $user = $this->userService->getUser($account->getAccountIdentifier(), $authenticationProviderName);
                    if ($user) {
                        if ($user->getElectronicAddresses()->count() <= 0) {
                            if (filter_var($account->getAccountIdentifier(), FILTER_VALIDATE_EMAIL)) {
                                $electronicAddress = new ElectronicAddress();
                                $electronicAddress->setIdentifier($account->getAccountIdentifier());
                                $user->setPrimaryElectronicAddress($electronicAddress);
                            } else {
                                throw new Exception('User "' . $account->getAccountIdentifier() . '" has no ElectronicAddress defined');
                            }
                        } else {
                            if (!$user->getPrimaryElectronicAddress()) {
                                $user->setPrimaryElectronicAddress($user->getElectronicAddresses()->first());
                            }
                        }

                        $isLoggedIn = true;
                        $comment->setEmail($user->getPrimaryElectronicAddress()->getIdentifier());
                        $comment->setFirstname($user->getName()->getFirstName());
                        $comment->setLastname($user->getName()->getLastName());
                        $comment->setAccount($account->getAccountIdentifier());
                    }
                }
            }
        }
        return $isLoggedIn;
    }
}
