<?php

namespace WebExcess\Comments\Domain\Model;

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

interface CommentInterface
{

    /**
     * @param NodeInterface|null $node
     */
    public function loadNodeData(NodeInterface $node = null);

    public function loadAccountDataIfAuthenticated();

    /**
     * @return string
     */
    public function getFirstname();

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname);

    /**
     * @return string
     */
    public function getLastname();

    /**
     * @param string $lastname
     */
    public function setLastname($lastname);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $message
     */
    public function setMessage($message);

    /**
     * @return bool
     */
    public function isNotify();

    /**
     * @param bool $notify
     */
    public function setNotify($notify);

    /**
     * @return string
     */
    public function getAccount();

    /**
     * @param string $account
     */
    public function setAccount($account);

    /**
     * @return string
     */
    public function getReference();

    /**
     * @param string $reference
     */
    public function setReference($reference);

    /**
     * @return string
     */
    public function getReCaptchaToken();

    /**
     * @param string $reCaptchaToken
     */
    public function setReCaptchaToken(string $reCaptchaToken);

}
