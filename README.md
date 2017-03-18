<p align="center">
    <img src="doc/assets/nelmio.png" width=300 />
</p>

<h1 align=center>Alice - Expressive fixtures generator</h1>

[![Package version](http://img.shields.io/packagist/vpre/nelmio/alice.svg?style=flat-square)](https://packagist.org/packages/nelmio/alice)
[![Build Status](https://img.shields.io/travis/nelmio/alice.svg?branch=2.x&style=flat-square)](https://travis-ci.org/nelmio/alice?branch=2.x)
[![Slack](https://img.shields.io/badge/slack-%23alice--fixtures-red.svg?style=flat-square)](https://slackinvite.me/to/symfony-devs)
[![License](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)

Relying on [fzaninotto/Faker](https://github.com/fzaninotto/Faker), Alice
allows you to create a ton of fixtures/fake data for use while developing
or testing your project. It gives you a few essential tools to make it
very easy to generate complex data with constraints in a readable and easy
to edit way, so that everyone on your team can tweak the fixtures if needed.

**You are reading the documentation for the 2.x branch. If you want to head back to the latest version, click
[here](https://github.com/nelmio).**
 
**2.x is in maintenance mode: PRs are accepted, but no active development is done on it by the maintainers any longer.**


## Table of Contents

1. [Installation](#installation)
1. [Example](#example)
1. [Getting Started](doc/getting-started.md)
    1. [Basic Usage](doc/getting-started.md#basic-usage)
    1. [Detailed Usage](doc/getting-started.md#detailed-usage)
1. [Complete Reference](doc/complete-reference.md)
    1. [Creating Fixtures](doc/complete-reference.md#creating-fixtures)
    1. [Fixture Ranges](doc/complete-reference.md#fixture-ranges)
    1. [Fixture Lists](doc/complete-reference.md#fixture-lists)
    1. [Calling Methods](doc/complete-reference.md#calling-methods)
    1. [Specifying Constructor Arguments](doc/complete-reference.md#specifying-constructor-arguments)
    1. [Custom Setter](doc/complete-reference.md#custom-setter)
    1. [Optional Data](doc/complete-reference.md#optional-data)
    1. [Handling Unique Constraints](doc/complete-reference.md#handling-unique-constraints)
1. [Handling Relations](doc/relations-handling.md)
    1. [References](doc/relations-handling.md#references)
    1. [Multiple References](doc/relations-handling.md#multiple-references)
    1. [Self reference](doc/relations-handling.md#self-reference)
    1. [Passing references to providers](doc/relations-handling.md#passing-references-to-providers)
1. [Keep Your Fixtures Dry](doc/fixtures-refactoring.md)
    1. [Fixture Inheritance](doc/fixtures-refactoring.md#fixture-inheritance)
    1. [Including files](doc/fixtures-refactoring.md#including-files)
    1. [Variables](doc/fixtures-refactoring.md#variables)
    1. [Parameters](doc/fixtures-refactoring.md#parameters)
1. [Customize Data Generation](doc/customizing-data-generation.md)
    1. [Faker Data](doc/customizing-data-generation.md#faker-data)
    1. [Reuse generated data using objects value](doc/customizing-data-generation.md#reuse-generated-data-using-objects-value)
    1. [Custom Faker Data Providers](doc/customizing-data-generation.md#custom-faker-data-providers)
1. [Event handling with Processors](doc/processors.md)
1. [Third-party libraries](#third-party-libraries)
    1. [Symfony](#symfony)
    1. [Nette](#nette)
    1. [Zend Framework 2](#zend-framework-2)

Other references:
  - [Tutorial: Using Alice in Symfony](https://knpuniversity.com/screencast/symfony-doctrine/fixtures-alice)


## Installation

This is installable via [Composer](https://getcomposer.org/) as [nelmio/alice](https://packagist.org/packages/nelmio/alice):

    composer require --dev nelmio/alice:^2.0


## Example

Here is a complete example of entity declaration:

```yaml
Nelmio\Entity\User:
    user{1..10}:
        username: '<username()>'
        fullname: '<firstName()> <lastName()>'
        birthDate: '<date()>'
        email: '<email()>'
        favoriteNumber: '50%? <numberBetween(1, 200)>'

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: '@user1'
        members: '<numberBetween(1, 10)>x @user*'
        created: '<dateTimeBetween("-200 days", "now")>'
        updated: '<dateTimeBetween($created, "now")>'
```

You can then load them easily with:

```php
$objects = \Nelmio\Alice\Fixtures::load(__DIR__.'/fixtures.yml', $objectManager);
```

For more information, refer to [the documentation](#table-of-contents).


## Third-party libraries

### Symfony

- [hautelook/AliceBundle](https://github.com/hautelook/AliceBundle)
- [h4cc/AliceFixturesBundle](https://github.com/h4cc/AliceFixturesBundle)
- [knplabs/rad-fixtures-load](https://github.com/KnpLabs/rad-fixtures-load)


### Nette

-  [Zenify/DoctrineFixtures](https://github.com/Zenify/DoctrineFixtures)


### Zend Framework 2:

- [ma-si/aist-alice-fixtures](https://github.com/ma-si/aist-alice-fixtures)
