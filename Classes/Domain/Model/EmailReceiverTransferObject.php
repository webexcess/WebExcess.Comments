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

use Neos\Flow\Reflection\ReflectionService;
use WebExcess\Comments\Domain\Model\CommentInterface;

class EmailReceiverTransferObject
{

    /**
     * @var array
     */
    protected $properties;

    /**
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * EmailReceiverTransferObject constructor.
     *
     * @param CommentInterface|null $comment
     */
    public function __construct(CommentInterface $comment = null)
    {
        if ($comment) {
            foreach (get_class_methods(get_class($comment)) as $methodName) {
                if (strpos($methodName, 'get') !== 0) {
                    continue;
                }

                $propertyName = lcfirst(substr($methodName, 3));

                $this->properties[$propertyName] = $comment->$methodName();
            }
        }
    }

    public function hasProperty($property)
    {
        return array_key_exists($property, $this->properties);
    }

    public function getProperty($property)
    {
        return $this->properties[$property];
    }

    public function setProperty($property, $value)
    {
        $this->properties[$property] = $value;
    }

}
