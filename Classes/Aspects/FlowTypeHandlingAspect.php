<?php

namespace WebExcess\Comments\Aspects;

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
     * @return array
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
