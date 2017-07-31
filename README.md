# WebExcess.Comments for Neos CMS
[![Latest Stable Version](https://poser.pugx.org/webexcess/comments/v/stable)](https://packagist.org/packages/webexcess/comments)
[![License](https://poser.pugx.org/webexcess/comments/license)](https://packagist.org/packages/webexcess/comments)

This package provides your visitors the possibility to comment stuff and discuss together.

## Compatibility and Maintenance

| Neos | Package | Maintained |
|------|---------|------------|
| 3.x  | 0.0.x   | YES        |

## Installation
```
composer require webexcess/comments
```

## Configuration
- **writeToDefaultDimension** (boolean)
  - true: Comments are written to your sites default dimension
  - false (default): Comments are written to the current users dimension
- **publishCommentsLive** (boolean)
  - true (default): Submitted comments are immediately visible in public
  - false: Submitted comments have to be published by a moderator ***(Not fully supported now)***
- **allowCommenting.account** (boolean)
  - true (default): Frontend-users comment with their account and account data
  - false: Frontend-user have to type in their data again
- **allowCommenting.guest:** (boolean)
  - true (default): A guest can comment
  - false: A guest can't comment *(just don't combine this with allowCommenting.account=false)*
- **repliesDepth** (int)
  - 0: No comment replies are allowed
  - 1: Only comments on the first level can get replies
  - n: ...
- **form.preset** (string)
  - Bootstrap (default): Base CSS-Framework for the form
  - Possible values are: WebExcess | Bootstrap | Foundation | Material | Float
- **mailer**
  - *@see code*


## Extension Points
- Fusion
  - Manipulating the comment listing,
  - User presentation,
  - etc.
- Form Template
  - Change the Form markup with a [Views.yaml](http://flowframework.readthedocs.io/en/stable/TheDefinitiveGuide/PartIII/ModelViewController.html#configuring-views-through-views-yaml) entry
- Email Template
  - Change the Email format and template in the packages settings
- Signals and Slots
  - The package sends the signal `commentCreated`. Read more about signals [here](http://flowframework.readthedocs.io/en/stable/TheDefinitiveGuide/PartIII/SignalsAndSlots.html).

### Extension and Integration Examples
- [Integrate in existing NodeType](Documentation/Examples/IntegrateInExistingNodeType.md)
- [Send a review Email on new Comments](Documentation/Examples/SendReviewEmail.md)
- [Extend the Comment Form](Documentation/Examples/ExtendTheCommentForm.md)
  - [Add a Property to Comments](Documentation/Examples/ExtendTheCommentForm.md#add-property)
  - [Change Form and FormField Templates](Documentation/Examples/ExtendTheCommentForm.md#change-form-template)


------------------------------------------

by [webexcess GmbH](https://webexcess.ch/)
