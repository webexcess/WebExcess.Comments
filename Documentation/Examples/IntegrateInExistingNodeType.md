# Integrate in existing NodeType

Let's assume we create a simple Blog-Package and wanna integrate [webexcess/comments](https://github.com/webexcess/WebExcess.Comments) in every Blog Post NodeType.

**Add the Comments Mixins to your NodeType**

_Vendor.Blog/Configuration/NodeTypes.Post.yaml_

	'Vendor.Blog:Post':
		superTypes:
			// ...
			'WebExcess.Comments:CommentsContentCollection': true
			'WebExcess.Comments:HideFormMixin': true
		// ...

**Add the Comments Rendering to your Prototype**

_Vendor.Blog/Resources/Private/Fusion/Pages/Post.fusion_

	prototype(Vendor.Blog:Post) {
		body.content.main {
	
			// ...
	
			comments = WebExcess.Comments:Content {
				@process.contentElementWrapping >
				content.form.@process.contentElementWrapping >
			}
		}
	}

**Make the ContentCollection available**

	flow node:repair --node-type Vendor.Blog:Post

**Result**

Now every every Blog-Post contains automatically the Comments-Section.
