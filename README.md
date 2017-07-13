# WebExcess.Comments Package for Neos CMS
[![Latest Stable Version](https://poser.pugx.org/webexcess/form/v/stable)](https://packagist.org/packages/webexcess/form)
[![License](https://poser.pugx.org/webexcess/form/license)](https://packagist.org/packages/webexcess/form)

This package provides your visitors the possibility to comment and discuss together.

## Compatibility and Maintenance

| Neos | Package | Maintained |
|------|---------|------------|
| 3.x  | 0.x     | YES        |

## Installation
```
composer require webexcess/comments
```

## Configuration
- **writeToDefaultDimension** (boolean)
  - true: Comments are written to your sites default dimension
  - false (default): Comments are written to the currents users dimension
- **publishCommentsLive** (boolean)
  - true (default): Submitted comments are immediately live visible
  - false: Submitted comments needs published of a moderator
- **allowCommenting.account** (boolean)
  - true (default): Frontend-users can comment with their account
  - false: Frontend-user have to type in their data again
- **allowCommenting.guest:** (boolean)
  - true (default): A guest can comment
  - false: A guest can't comment
- **repliesDepth** (int)
  - 0: No comment replies are allowed
  - 1: Only first-level comments can get replies
  - n: ...
- **form.preset** (string)
  - default: Bootstrap
  - possible: WebExcess | Bootstrap | Foundation | Material | Float
- **mailer**
  - tbd


## Extension Points
- Fusion
  - Manipulating the comment listing,
  - User presentation,
  - etc.
- Form Template
  - Change the Form markup with an [Views.yaml](http://flowframework.readthedocs.io/en/stable/TheDefinitiveGuide/PartIII/ModelViewController.html#configuring-views-through-views-yaml) entry
- Email Template
  - Change the Email format and template in the packages settings and custom templates
- Signals and Slots
  - The package sends the signal `commentCreated`. Read more about signal [here](http://flowframework.readthedocs.io/en/stable/TheDefinitiveGuide/PartIII/SignalsAndSlots.html).


------------------------------------------

by [webexcess GmbH](https://webexcess.ch/)