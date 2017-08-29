<?php

namespace WebExcess\Comments\Aspects;

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
use WebExcess\Comments\Domain\Model\CommentInterface;

/**
 * @Flow\Aspect
 * @Flow\Scope("singleton")
 */
class FlowTypeHandlingAspect
{

    /**
     * @var ObjectManager
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @Flow\Around("method(Neos\Flow\Mvc\Controller\Argument->__construct())")
     * @param \Neos\Flow\AOP\JoinPointInterface $joinPoint
     * @return void
     */
    public function handleCommentType(\Neos\Flow\AOP\JoinPointInterface $joinPoint)
    {
        $type = $joinPoint->getMethodArgument('dataType');

        if ($type == CommentInterface::class) {
            // Set the final domain model for propper mvc controller action validation..
            $joinPoint->setMethodArgument(
                'dataType',
                $this->objectManager->getClassNameByObjectName(CommentInterface::class)
            );
        }

        // Execute the original constructor..
        $joinPoint->getAdviceChain()->proceed($joinPoint);
    }

}
