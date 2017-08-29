<?php

namespace WebExcess\Comments\Service;

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
use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Form\Exception\FinisherException;
use Neos\FluidAdaptor\View\StandaloneView;
use Neos\SwiftMailer;
use WebExcess\Comments\Domain\Model\CommentInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Log\SystemLoggerInterface;
use WebExcess\Comments\Domain\Model\EmailReceiverTransferObject;

/**
 * @Flow\Scope("singleton")
 */
class Mailer
{
    /**
     * @Flow\InjectConfiguration(path="mailer", package="WebExcess.Comments")
     * @var array
     */
    protected $settings;

    /**
     * @Flow\Inject()
     * @var NodeUriBuilder
     */
    protected $uriBuilder;

    /**
     * @var SystemLoggerInterface
     * @Flow\Inject
     */
    protected $logger;

    /**
     * @var ObjectManager
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @param CommentInterface $comment
     * @param NodeInterface $commentNode
     */
    public function sendCommentCreatedEmails(CommentInterface $comment, NodeInterface $commentNode)
    {
        $q = new FlowQuery(array($commentNode));

        /** @var NodeInterface $documentNode */
        $documentNode = $q->closest('[instanceof Neos.Neos:Document]')->get(0);

        $recipients = $this->collectRecipientsByCommentNode($commentNode);

        foreach ($recipients as $recipient) {
            $this->sendCommentCreatedEmail($comment, $commentNode, $recipient, $documentNode);
        }
    }

    /**
     * @param NodeInterface $commentNode
     * @return array
     */
    protected function collectRecipientsByCommentNode(NodeInterface $commentNode)
    {
        $q = new FlowQuery(array($commentNode));

        /** @var NodeInterface $documentNode */
        $documentNode = $q->closest('[instanceof Neos.Neos:Document]')->get(0);

        $threadNodes = $q->parent()->parent()->find('[instanceof WebExcess.Comments:Comment]')->get();
        if ($q->parent()->parent()->is('[instanceof WebExcess.Comments:Comment]')) {
            $threadNodes[] = $q->parent()->parent()->get(0);
        }

        $recipients = array();
        foreach ($threadNodes as $threadNode) {
            if ($threadNode->getProperty('notify') && $threadNode->getProperty('email') != $commentNode->getProperty('email')) {
                $comment = $this->objectManager->get(CommentInterface::class);
                $comment->loadNodeData($threadNode);
                $recipients[sha1($threadNode->getProperty('email'))] = new EmailReceiverTransferObject($comment);
            }
        }

        return $recipients;
    }

    /**
     * @param CommentInterface $comment
     * @param NodeInterface $commentNode
     * @param EmailReceiverTransferObject $recipient
     * @param NodeInterface $documentNode
     */
    protected function sendCommentCreatedEmail(CommentInterface &$comment, NodeInterface &$commentNode, EmailReceiverTransferObject $recipient, NodeInterface &$documentNode)
    {
        $standaloneView = $this->initializeStandaloneView('commentCreatedView');
        $standaloneView->assign('documentIdentifier', $documentNode->getIdentifier());
        $standaloneView->assign('documentUri', $this->uriBuilder->getUriToNode($documentNode));
        $standaloneView->assign('comment', $comment);
        $standaloneView->assign('firstname', $recipient->getProperty('firstname'));
        $standaloneView->assign('lastname', $recipient->getProperty('lastname'));
        $message = $standaloneView->render();

        $fromAddress = $this->settings['fromAddress'];
        $fromName = !empty($this->settings['fromName']) ? $this->settings['fromName'] : $fromAddress;
        $replyToAddress = $this->settings['replyToAddress'];
        $carbonCopyAddress = $this->settings['carbonCopyAddress'];
        $blindCarbonCopyAddress = $this->settings['blindCarbonCopyAddress'];

        $subject = $this->settings['subject'];
        $recipientAddress = $recipient->getProperty('email');
        $recipientName = $recipient->getProperty('firstname') . ' ' . $recipient->getProperty('lastname');

        $mail = new SwiftMailer\Message();
        $mail->setFrom(array($fromAddress => $fromName))
            ->setTo(array($recipientAddress => $recipientName))
            ->setSubject($subject);

        if ($replyToAddress) {
            $mail->setReplyTo($replyToAddress);
        }
        if ($carbonCopyAddress) {
            $mail->setCc($carbonCopyAddress);
        }
        if ($blindCarbonCopyAddress) {
            $mail->setBcc($blindCarbonCopyAddress);
        }

        if ($this->settings['commentCreatedView']['format'] == 'html') {
            $mail->setBody($message, 'text/html');
        } else {
            $mail->setBody($message, 'text/plain');
        }

        if ($this->settings['testMode']) {
            $this->logger->log(sprintf('CommentCreatedEmail to %s (%s) sent.', $recipientAddress, $recipientName), LOG_INFO, array('message' => $message));
        } else {
            $mail->send();
        }
    }

    /**
     * @param string $view
     * @return StandaloneView
     * @throws FinisherException
     */
    protected function initializeStandaloneView($view)
    {
        $standaloneView = new StandaloneView();
        if (!isset($this->settings[$view]['templatePathAndFilename'])) {
            throw new FinisherException('The setting "templatePathAndFilename" must be set.');
        }
        $standaloneView->setTemplatePathAndFilename($this->settings[$view]['templatePathAndFilename']);

        if (isset($this->settings[$view]['partialRootPath'])) {
            $standaloneView->setPartialRootPath($this->settings[$view]['partialRootPath']);
        }

        if (isset($this->settings[$view]['layoutRootPath'])) {
            $standaloneView->setLayoutRootPath($this->settings[$view]['layoutRootPath']);
        }

        if (isset($this->settings[$view]['variables'])) {
            $standaloneView->assignMultiple($this->settings[$view]['variables']);
        }

        return $standaloneView;
    }

}
