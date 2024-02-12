# Newsletter Signup Block

This module simply creates a newsletter signup block which drives users
to a signup page or the full signup process can be completed on the short
form. Works best in conjunction with Webform module.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/newsletter_signup_block).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/newsletter_signup_block).


## Requirements

- Drupal 8.x || 9.x
- [Webform module](https://www.drupal.org/project/webform)
- [Token module](https://www.drupal.org/project/token)


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

1. Setup new block through block layout screen or using the block
   field with Paragraphs.
2. Configure block as needed and any other required fields.
3. If form method "POST" was selected, you will need to set the
   default value on Webform fields to use one of the custom tokens
   below:

```php
[newsletter_signup_block:submitted_email]
[newsletter_signup_block:submitted_first_name]
[newsletter_signup_block:submitted_last_name]
```

If form method "GET" was selected, you will need to turn on
"**Allow all elements to be populated using query string parameters**"
option, which is found on the Webform settings screen.


## Maintainers

- George Anderson - [geoanders](https://www.drupal.org/u/geoanders)
- Michael O'Hara - [mikeohara](https://www.drupal.org/u/mikeohara)
