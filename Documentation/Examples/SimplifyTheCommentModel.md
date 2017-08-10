# Simplify the Comment-Model

Let's assume only authenticated Frontend-Users care allowed to create comments.. so we can remove the obsolete NodeData Properties and load the Data directly form the Users.

**Remove the Properties from the NodeType and Inspector Section<a name="remove-property"></a>**

_Vendor.Blog/Configuration/NodeTypes.Comment.yaml_

	'WebExcess.Comments:Comment':
		properties:
			firstname: null
			lastname: null
			email: null

**Create a custom Comment-Model which includes the new Property**

_Vendor.Blog/Classes/Vendor/Blog/Domain/Model/Comment.php_

	<?php
	
	namespace Vendor\Blog\Domain\Model;
	
	use Neos\Flow\Annotations as Flow;
	use WebExcess\Comments\Domain\Model\CommentAbstract;
	use WebExcess\Comments\Domain\Model\CommentInterface;
	
	class Comment extends CommentAbstract implements CommentInterface
	{
		// Override loadNodeData(..) an loadAccountDataIfAuthenticated() if needed..
	}

**Replace the original Comment-Model with your version**

_Vendor.Blog/Classes/Vendor/Blog/Configuration/Objects.yaml_

	WebExcess\Comments\Domain\Model\CommentInterface:
		className: 'Vendor\Blog\Domain\Model\Comment'

**Point to your custom Templates<a name="change-form-template"></a>**

_Vendor.Blog/Configuration/Views.yaml_

	-
		requestFilter: 'isPackage("WebExcess.Comments") && isController("Comments")'
		options:
			templatePathAndFilename: 'resource://Vendor.Blog/Private/Templates/Comments/Index.html'

**Copy the Base-Template and remove unused Form-Fields**

_Vendor.Blog/Resources/Private/Templates/Comments/Index.html_

	<!-- ... -->

**Result**

Now your Blog-Package is using [webexcess/comments](https://github.com/webexcess/WebExcess.Comments) and does not store Commenter-Data twice.

Without ~~breaking~~ forking the original Package itself.
