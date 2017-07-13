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

class Comment
{

    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     */
    protected $firstname;

    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     */
    protected $lastname;

    /**
     * @var string
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="EmailAddress")
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
}
