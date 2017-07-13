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
use Neos\Form\Exception\FinisherException;
use Neos\FluidAdaptor\View\StandaloneView;
use Neos\SwiftMailer;
use WebExcess\Comments\Domain\Model\Comment;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\ContentRepository\Domain\Model\Node;
use Neos\Flow\Log\SystemLoggerInterface;

/**
 * @Flow\Scope("singleton")
 */
class Mailer
{
    /**
     * @Flow\InjectConfiguration(package="WebExcess.Comments")
     * @var string
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
     * @param Comment $comment
     * @param Node $commentNode
     */
    public function sendCommentCreatedEmails(Comment $comment, Node $commentNode)
    {
        $q = new FlowQuery(array($commentNode));

        /** @var Node $documentNode */
        $documentNode = $q->closest('[instanceof Neos.Neos:Document]')->get(0);

        $threadNodes = $q->parent()->parent()->find('[instanceof WebExcess.Comments:Comment]')->get();
        if ($q->parent()->parent()->is('[instanceof WebExcess.Comments:Comment]')) {
            $threadNodes[] = $q->parent()->parent()->get(0);
        }
        $recipientNodes = array();
        foreach ($threadNodes as $threadNode) {
            if ($threadNode->getProperty('notify') && $threadNode->getProperty('email') != $commentNode->getProperty('email')) {
                $recipientNodes[sha1($threadNode->getProperty('email'))] = $threadNode;
            }
        }

        foreach ($recipientNodes as $recipientNode) {
            $this->sendCommentCreatedEmail($comment, $commentNode, $recipientNode, $documentNode);
        }
    }

    /**
     * @param Comment $comment
     * @param Node $commentNode
     * @param Node $recipientNode
     * @param Node $documentNode
     */
    private function sendCommentCreatedEmail(Comment &$comment, Node &$commentNode, Node &$recipientNode, Node &$documentNode)
    {
        $standaloneView = $this->initializeStandaloneView('commentCreatedView');
        $standaloneView->assign('documentIdentifier', $documentNode->getIdentifier());
        $standaloneView->assign('documentUri', $this->uriBuilder->getUriToNode($documentNode));
        $standaloneView->assign('comment', $comment);
        $standaloneView->assign('firstname', $recipientNode->getProperty('firstname'));
        $standaloneView->assign('lastname', $recipientNode->getProperty('lastname'));
        $message = $standaloneView->render();

        $fromAddress = $this->settings['mailer']['fromAddress'];
        $fromName = !empty($this->settings['mailer']['fromName']) ? $this->settings['mailer']['fromName'] : $fromAddress;
        $replyToAddress = $this->settings['mailer']['replyToAddress'];
        $carbonCopyAddress = $this->settings['mailer']['carbonCopyAddress'];
        $blindCarbonCopyAddress = $this->settings['mailer']['blindCarbonCopyAddress'];

        $subject = '';
        $recipientAddress = $recipientNode->getProperty('email');
        $recipientName = $recipientNode->getProperty('firstname') . ' ' . $recipientNode->getProperty('lastname');

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

        if ($this->settings['mailer']['commentCreatedView']['format'] == 'html') {
            $mail->setBody($message, 'text/html');
        } else {
            $mail->setBody($message, 'text/plain');
        }

        if ($this->settings['mailer']['testMode']) {
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
        if (!isset($this->settings['mailer'][$view]['templatePathAndFilename'])) {
            throw new FinisherException('The setting "templatePathAndFilename" must be set.');
        }
        $standaloneView->setTemplatePathAndFilename($this->settings['mailer'][$view]['templatePathAndFilename']);

        if (isset($this->settings['mailer'][$view]['partialRootPath'])) {
            $standaloneView->setPartialRootPath($this->settings['mailer'][$view]['partialRootPath']);
        }

        if (isset($this->settings['mailer'][$view]['layoutRootPath'])) {
            $standaloneView->setLayoutRootPath($this->settings['mailer'][$view]['layoutRootPath']);
        }

        if (isset($this->settings['mailer'][$view]['variables'])) {
            $standaloneView->assignMultiple($this->settings['mailer'][$view]['variables']);
        }

        return $standaloneView;
    }

}
