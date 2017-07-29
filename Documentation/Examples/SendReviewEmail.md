# Send a review Email on new Comments

Let's assume we have to send an Email to the Administrator on every new Comment.

**Add your Administrator's Email Address to every Receptions Email-Collection**

_Vendor.Blog/Classes/Vendor/Blog/Aspects/CommentCollectRecipientsAspect.php_

	<?php
	
	namespace Vendor\Blog\Aspects;
	
	use Neos\ContentRepository\Domain\Model\NodeInterface;
	use Neos\Flow\Annotations as Flow;
	use Neos\Eel\FlowQuery\FlowQuery;
	use Neos\ContentRepository\Domain\Model\Node;
	use WebExcess\Comments\Domain\Model\EmailReceiverTransferObject;
	
	/**
	 * @Flow\Aspect
	 * @Flow\Scope("singleton")
	 */
	class CommentCollectRecipientsAspect
	{
	
		/**
		 * @Flow\Around("method(WebExcess\Comments\Service\Mailer->collectRecipientsByCommentNode())")
		 * @param \Neos\Flow\AOP\JoinPointInterface $joinPoint
		 * @return array
		 */
		public function addModeratorRecipient(\Neos\Flow\AOP\JoinPointInterface $joinPoint)
		{
			$commentNode = $joinPoint->getMethodArgument('commentNode');
			$recipients = $joinPoint->getAdviceChain()->proceed($joinPoint);
	
			$receiver = new EmailReceiverTransferObject();
			$receiver->setProperty('firstname', 'John');
			$receiver->setProperty('lastname', 'Doe');
			$receiver->setProperty('email', 'john.doe@vendor.com');
	
			$recipients[sha1($receiver->getProperty('email'))] = $receiver;
	
			return $recipients;
		}
	
	}

**Result**

Now everytime a notification is sent to a collection of receivers, your Administrator gets added to the list.

Without ~~breaking~~ forking the original Package itself.
