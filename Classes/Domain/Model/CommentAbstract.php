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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Context;
use Neos\Neos\Domain\Service\UserService;
use Neos\ContentRepository\Domain\Model\NodeInterface;

class CommentAbstract implements CommentInterface
{

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
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     */
    protected $message;

    /**
     * @var boolean
     */
    protected $notify = true;

    /**
     * @var string
     */
    protected $account;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var string
     * @Flow\Validate(type="WebExcess.Comments:ReCaptcha")
     */
    protected $reCaptchaToken;

    /**
     * @var \DateTime
     */
    protected $publishingDate = null;

    public function loadNodeData(NodeInterface $node = null)
    {
        if (!is_null($node)) {
            $this->firstname = $node->getProperty('firstname');
            $this->lastname = $node->getProperty('lastname');
            $this->email = $node->getProperty('email');
            $this->message = $node->getProperty('message');
            $this->notify = $node->getProperty('notify');
            $this->account = $node->getProperty('account');
            $this->reference = $node->getProperty('reference');
            $this->publishingDate = $node->getProperty('publishingDate');
        }
    }

    public function loadAccountDataIfAuthenticated()
    {
        $isLoggedIn = false;
        $authenticationTokens = $this->securityContext->getAuthenticationTokens();
        if (!empty($authenticationTokens)) {
            $account = $this->securityContext->getAccount();
            if ($account !== null) {
                foreach ($authenticationTokens as $authenticationProviderName => $obj) {
                    $user = $this->userService->getUser($account->getAccountIdentifier(), $authenticationProviderName);
                    if ($user) {
                        $emailAddress = null;
                        if ($user->getElectronicAddresses()->count() <= 0) {
                            if (filter_var($account->getAccountIdentifier(), FILTER_VALIDATE_EMAIL)) {
                                $emailAddress = $account->getAccountIdentifier();
                            }
                        } else {
                            if ($user->getPrimaryElectronicAddress()) {
                                $emailAddress = $user->getPrimaryElectronicAddress()->getIdentifier();
                            } else {
                                $emailAddress = $user->getElectronicAddresses()->first()->getIdentifier();
                            }
                        }

                        $isLoggedIn = true;
                        if ($emailAddress !== null) {
                            $this->setEmail($emailAddress);
                        }
                        $this->setFirstname($user->getName()->getFirstName());
                        $this->setLastname($user->getName()->getLastName());
                        $this->setAccount($account->getAccountIdentifier());
                    }
                }
            }
        }
        return $isLoggedIn;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $firstname = strip_tags($firstname);
        $firstname = preg_replace('/(?:(?:\r\n|\r|\n)\s*){2}/s', ' ', $firstname);
        $firstname = preg_replace('/[ \t]+/', ' ', $firstname);
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $lastname = strip_tags($lastname);
        $lastname = preg_replace('/(?:(?:\r\n|\r|\n)\s*){2}/s', ' ', $lastname);
        $lastname = preg_replace('/[ \t]+/', ' ', $lastname);
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $message = strip_tags($message);
        $message = preg_replace('/(?:(?:\r\n|\r|\n)\s*){2}/s', "\n", $message);
        $message = preg_replace('/[ \t]+/', ' ', $message);
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isNotify()
    {
        return $this->notify;
    }

    /**
     * @param bool $notify
     */
    public function setNotify($notify)
    {
        $this->notify = $notify;
    }

    /**
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param string $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getReCaptchaToken()
    {
        return $this->reCaptchaToken;
    }

    /**
     * @param string $reCaptchaToken
     */
    public function setReCaptchaToken(string $reCaptchaToken)
    {
        $this->reCaptchaToken = $reCaptchaToken;
    }

    /**
     * @return \DateTime
     */
    public function getPublishingDate()
    {
        return $this->publishingDate;
    }

    /**
     * @param \DateTime $publishingDate
     */
    public function setPublishingDate(\DateTime $publishingDate)
    {
        $this->publishingDate = $publishingDate;
    }

}
