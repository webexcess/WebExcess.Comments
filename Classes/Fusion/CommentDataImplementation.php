<?php

namespace WebExcess\Comments\Fusion;

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
use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use WebExcess\Comments\Domain\Model\CommentInterface;
use Neos\Fusion\FusionObjects\ArrayImplementation;

class CommentDataImplementation extends ArrayImplementation
{

    /**
     * @var ObjectManager
     * @Flow\Inject
     */
    protected $objectManager;

    public function evaluate()
    {
        /** @var NodeInterface $node */
        $node = $this->fusionValue('node');

        $comment = $this->objectManager->get(CommentInterface::class);
        $comment->loadNodeData($node);

        $output = array(
            'identifier' => $node->getIdentifier()
        );

        foreach (get_class_methods(get_class($comment)) as $methodName) {
            if (strpos($methodName, 'get') !== 0) {
                continue;
            }

            $propertyName = lcfirst(substr($methodName, 3));
            $output[$propertyName] = $comment->$methodName();
        }

        return $output;
    }

}
