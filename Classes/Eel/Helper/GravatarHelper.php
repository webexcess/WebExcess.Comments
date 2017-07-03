<?php
namespace WebExcess\Comments\Eel\Helper;

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
use Neos\Eel\ProtectedContextAwareInterface;

/**
 * @Flow\Proxy(false)
 */
class GravatarHelper implements ProtectedContextAwareInterface
{
    /**
     * Concatenate arrays or values to a new array
     *
     * @param string $email
     * @param string $default
     * @param integer $size
     * @return string
     */
    public function uri($email, $default = 'mm', $size = 80)
    {
        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=' . $default . '&s=' . $size;
    }

    /**
     * All methods are considered safe
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
