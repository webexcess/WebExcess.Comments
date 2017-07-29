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

class EmailReceiverTransferObject
{

    /**
     * @var array
     */
    protected $properties;

    /**
     * EmailReceiverTransferObject constructor.
     *
     * @param NodeInterface|null $node
     */
    public function __construct(NodeInterface $node = null)
    {
        if ($node) {
            $this->properties = $node->getProperties();
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
