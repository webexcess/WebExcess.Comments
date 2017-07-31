# Extend the Comment Form

Let's assume we create a Blog-Package and have to add an optional phone field to the comment form.

**Add the new Property to the NodeType and Inspector Section<a name="add-property"></a>**

_Vendor.Blog/Configuration/NodeTypes.Comment.yaml_

	'WebExcess.Comments:Comment':
		properties:
			phone:
				type: string
				ui:
					inlineEditable: false
					label: 'Vendor.Blog:NodeTypes.Comment:properties.phone'
					reloadIfChanged: true
					inspector:
						group: 'comment'
						editorOptions:
							maxlength: 50

**Introduce the new Property to the Domain Model**

_Vendor.Blog/Classes/Vendor/Blog/Aspects/CommentPropertyIntroductionAspect.php_

	<?php
	
	namespace Vendor\Blog\Aspects;
	
	use Neos\Flow\Annotations as Flow;

	/**
	 * @Flow\Aspect
	 */
	class CommentPropertyIntroductionAspect
	{
	
		/**
		 * @var string
		 * @Flow\Introduce("class(WebExcess\Comments\Domain\Model\Comment)")
		 */
		protected $phone;
	
	}

**Introduce a Trait which enables you to add new Methods to the Model**

_Vendor.Blog/Classes/Vendor/Blog/Aspects/CommentPropertyTraitIntroductionAspect.php_

	<?php
	
	namespace Vendor\Blog\Aspects;
	
	use Neos\Flow\Annotations as Flow;
	
	/**
	 * @Flow\Introduce("class(WebExcess\Comments\Domain\Model\Comment)", traitName="Vendor\Blog\Traits\CommentPropertyTrait")
	 * @Flow\Aspect
	 */
	class CommentPropertyTraitIntroductionAspect
	{
	}

**Use a Trait which inserts the new Getter and Setter**

_Vendor.Blog/Classes/Vendor/Blog/Traits/CommentPropertyTrait.php_

	<?php
	
	namespace Vendor\Blog\Traits;
	
	trait CommentPropertyTrait
	{
	
		/**
		 * @return string
		 */
		public function getPhone()
		{
			return $this->phone;
		}
		
		/**
		 * @param string $phone
		 */
		public function setPhone($phone)
		{
			$this->phone = $phone;
		}
	
	}

**Point to your custom Templates<a name="change-form-template"></a>**

_Vendor.Blog/Configuration/Views.yaml_

	-
		requestFilter: 'isPackage("WebExcess.Comments") && isController("Comments")'
		options:
			templatePathAndFilename: 'resource://Vendor.Blog/Private/Templates/Comments/Index.html'
			partialRootPaths: ['resource://Vendor.Blog/Private/Partials/Comments']

**Copy the Base-Template and add your Form-Field**

_Vendor.Blog/Resources/Private/Templates/Comments/Index.html_

	<!-- ... -->
	<div class="row">
		<div class="col-sm-4">
			<f:render partial="TextField" arguments="{fieldname: 'firstname', required: true}" />
		</div>
		<div class="col-sm-4">
			<f:render partial="TextField" arguments="{fieldname: 'lastname', required: true}" />
		</div>
	</div>
	<div class="row">
		<div class="col-sm-4">
			<f:render partial="TextField" arguments="{fieldname: 'email', required: true, type: 'email'}" />
		</div>
		<div class="col-sm-4">
			<f:render partial="TextField" arguments="{fieldname: 'phone'}" />
		</div>
	</div>
	<!-- ... -->

**Overwrite the TextField-Partial to inject your custom Translation-File**

_Vendor.Blog/Resources/Private/Partials/Comments/TextField.html_

	<f:form.validationResults for="comment.{fieldname}">
		<div class="mf-input{f:if(condition: validationResults.flattenedErrors, then: ' mf-has-error')}">
			<f:form.textfield property="{fieldname}" id="{fieldname}" type="{f:if(condition: type, then: type, else: 'text')}" class="mf-input-field" />
			<div class="mf-input-bar"></div>
			<label for="{fieldname}" class="mf-input-label"><f:translate id="properties.phone.{fieldname}" package="Vendor.Blog" source="NodeTypes/Comment" value="{f:translate(id: 'comment.form.{fieldname}', package: 'WebExcess.Comments')}" />{f:if(condition: required, then: '<sup class="is-required">*</sup>')}</label>
			<f:render partial="Validation" arguments="{fieldname: fieldname}" />
		</div>
	</f:form.validationResults>

**Add your field translation**

_Vendor.Blog/Resources/Private/Translations/en/NodeTypes/Comment.xlf_

	<?xml version="1.0" encoding="UTF-8"?>
	<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
		<file original="" product-name="Vendor.Blog" source-language="en" datatype="plaintext">
			<body>
				<trans-unit id="properties.phone" xml:space="preserve">
					<source>Phone</source>
				</trans-unit>
			</body>
		</file>
	</xliff>

**Result**

Now your Blog-Package is using [webexcess/comments](https://github.com/webexcess/WebExcess.Comments) for all Feedbacks and offers an optional Phone-Formfield which is only visible to the editors.
So customers can open another feedback-channel.

Without ~~breaking~~ forking the original Package itself.
