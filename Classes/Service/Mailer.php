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
use Neos\Form\Core\Model\AbstractFinisher;
use Neos\Form\Exception\FinisherException;
use Neos\FluidAdaptor\View\StandaloneView;
use Neos\SwiftMailer;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use WebExcess\Comments\Domain\Model\Comment;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\ContentRepository\Domain\Model\Node;

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

    const FORMAT_PLAINTEXT = 'plaintext';
    const FORMAT_HTML = 'html';

    /**
     * @var array
     */
    protected $defaultOptions = array(
        'recipientName' => '',
        'senderName' => '',
        'excludeFields' => array(),
        'format' => self::FORMAT_HTML,
        'testMode' => false,
        'debugMessage' => false,
    );

    /**
     * @param Comment $comment
     * @param Node $commentNode
     */
    public function sendCommentCreatedEmails(Comment $comment, Node $commentNode)
    {
        $q = new FlowQuery(array($commentNode));

        /** @var Node $documentNode */
        // @todo parentsUntil is not working now..
        $documentNode = $q->parentsUntil('[instanceof Neos.Neos:Document]')->parent()->get(0);

        /** @var Node $parentNode */
        $parentNode = $q->parent()->parent();
        $threadNodes = $parentNode->find('[instanceof WebExcess.Comments:Comment]')->get();
        $receptionNodes = array();
        foreach ($threadNodes as $threadNode) {
            if ($threadNode->getProperty('notify') && $threadNode->getProperty('email') != $commentNode->getProperty('email')) {
                $receptionNodes[sha1($threadNode->getProperty('email'))] = $threadNode;
            }
        }

        foreach ($receptionNodes as $receptionNode) {
            $this->sendCommentCreatedEmail($comment, $commentNode, $receptionNode, $documentNode);
        }
        exit();
    }

    /**
     * @param Comment $comment
     * @param Node $commentNode
     * @param Node $receptionNode
     * @param Node $documentNode
     */
    private function sendCommentCreatedEmail(Comment &$comment, Node &$commentNode, Node &$receptionNode, Node &$documentNode)
    {
        $standaloneView = $this->initializeStandaloneView('commentCreatedView');
        $standaloneView->assign('documentIdentifier', $documentNode->getIdentifier());
        $standaloneView->assign('comment', $comment);
        $standaloneView->assign('firstname', $receptionNode->getProperty('firstname'));
        $standaloneView->assign('lastname', $receptionNode->getProperty('lastname'));
        $message = $standaloneView->render();

        $fromAddress = $this->settings['mailer']['fromAddress'];
        $fromName = !empty($this->settings['mailer']['fromName']) ? $this->settings['mailer']['fromName'] : $fromAddress;
        $replyToAddress = $this->settings['mailer']['replyToAddress'];
        $carbonCopyAddress = $this->settings['mailer']['carbonCopyAddress'];
        $blindCarbonCopyAddress = $this->settings['mailer']['blindCarbonCopyAddress'];

        $subject = '';
        $recipientAddress = $receptionNode->getProperty('email');
        $recipientName = $receptionNode->getProperty('firstname') . ' ' . $receptionNode->getProperty('lastname');

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

        $mail->setBody($message, 'text/plain');

        \Neos\Flow\var_dump($message);
        return;
        $mail->send();
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
