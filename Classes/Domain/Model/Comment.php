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

class Comment extends CommentAbstract implements CommentInterface
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

}
