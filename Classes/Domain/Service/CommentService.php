<?php

namespace WebExcess\Comments\Domain\Service;

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
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface;
use WebExcess\Comments\Domain\Model\CommentInterface;
use Neos\Neos\Exception;

/**
 * @Flow\Scope("singleton")
 */
class CommentService
{
    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\InjectConfiguration()
     * @var array
     */
    protected $settings;

    /**
     * @Flow\Inject
     * @var ContentDimensionPresetSourceInterface
     */
    protected $contentDimensionPresetSource;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @Flow\Inject
     * @var NodeTypeManager
     */
    protected $nodeTypeManager;


    /**
     * @param CommentInterface $comment
     * @param NodeInterface $documentNode
     * @throws Exception
     */
    public function addComment(CommentInterface $comment, NodeInterface $documentNode)
    {
        $q = new FlowQuery(array($documentNode));
        $storageNodeQuery = $q->find('[instanceof WebExcess.Comments:Content]')->count() > 0 ? $q->find('[instanceof WebExcess.Comments:Content]') : $q;

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
        $commentsCollection = $storageNodeQuery->children('comments')->context(['workspaceName' => 'live', 'dimensions' => $dimensions, 'targetDimensions' => $targetDimension])->get(0);
        if ($comment->getReference() != '') {
            $commentsCollection = $storageNodeQuery->find('#' . $comment->getReference())->children('comments')->context(['workspaceName' => 'live', 'dimensions' => $dimensions, 'targetDimensions' => $targetDimension])->get(0);
        }

        // Make sure, that no account-data gets overwitten by post-data..
        $comment->loadAccountDataIfAuthenticated();

        if ($commentsCollection !== null) {
            $propertyNames = $this->reflectionService->getClassPropertyNames(get_class($comment));
            $commentNodeType = $this->nodeTypeManager->getNodeType('WebExcess.Comments:Comment');

            $newCommentNode = $commentsCollection->createNode(uniqid('comment-'), $commentNodeType);
            $newCommentNode->setHidden(!$this->settings['publishCommentsLive']);
            $newCommentNode->setProperty('publishingDate', new \DateTime());

            foreach ($propertyNames as $propertyName) {
                if ($propertyName == 'reCaptchaToken' || $propertyName == 'publishingDate') {
                    continue;
                }

                $methodName = $propertyName == 'notify' ? 'is' . ucfirst($propertyName) : 'get' . ucfirst($propertyName);
                if (method_exists($comment, $methodName)) {
                    $method = $methodName;
                    if (array_key_exists($propertyName, $commentNodeType->getProperties())) {
                        $newCommentNode->setProperty($propertyName, $comment->$method());
                    }
                }
            }

            $this->persistenceManager->persistAll();

            $this->emitCommentCreated($comment, $newCommentNode);

        } else {
            throw new Exception('No "comments" ContentCollection found');
        }
    }

    /**
     * @param CommentInterface $comment
     * @param NodeInterface $commentNode
     * @throws Exception
     */
    public function updateComment(CommentInterface $comment, NodeInterface $commentNode)
    {
        // Make sure, that no account-data gets overwitten by post-data..
        $comment->loadAccountDataIfAuthenticated();

        $propertyNames = $this->reflectionService->getClassPropertyNames(get_class($comment));
        $commentNodeType = $this->nodeTypeManager->getNodeType('WebExcess.Comments:Comment');

        $commentNode->setHidden(!$this->settings['publishCommentsLive']);
        $commentNode->setProperty('publishingDate', new \DateTime());

        foreach ($propertyNames as $propertyName) {
            if ($propertyName == 'reCaptchaToken' || $propertyName == 'publishingDate') {
                continue;
            }

            $methodName = $propertyName == 'notify' ? 'is' . ucfirst($propertyName) : 'get' . ucfirst($propertyName);
            if (method_exists($comment, $methodName)) {
                $method = $methodName;
                if (array_key_exists($propertyName, $commentNodeType->getProperties())) {
                    $commentNode->setProperty($propertyName, $comment->$method());
                }
            }
        }

        $this->persistenceManager->persistAll();

        $this->emitCommentUpdated($comment, $commentNode);
    }

    /**
     * @param CommentInterface $comment
     * @param NodeInterface $commentNode
     * @return void
     * @Flow\Signal
     */
    protected function emitCommentCreated(CommentInterface $comment, NodeInterface $commentNode)
    {
    }

    /**
     * @param CommentInterface $comment
     * @param NodeInterface $commentNode
     * @return void
     * @Flow\Signal
     */
    protected function emitCommentUpdated(CommentInterface $comment, NodeInterface $commentNode)
    {
    }
}
