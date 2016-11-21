Alice - Expressive fixtures generator
=====================================

[![Package version](http://img.shields.io/packagist/vpre/nelmio/alice.svg?style=flat-square)](https://packagist.org/packages/nelmio/alice)
[![Build Status](https://img.shields.io/travis/nelmio/alice.svg?branch=master&style=flat-square)](https://travis-ci.org/nelmio/alice?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/25fb7890-61cd-4b1b-96b8-33b4db7ec212.svg?style=flat-square)](https://insight.sensiolabs.com/projects/25fb7890-61cd-4b1b-96b8-33b4db7ec212)
[![Dependency Status](https://www.versioneye.com/user/projects/5813cac941d4bc00128b57b3/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5813cac941d4bc00128b57b3)
[![Gitter](https://img.shields.io/gitter/room/nelmio/alice.svg?style=flat)](https://gitter.im/hautelook/AliceBundle?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)
[![License](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)


Relying on [fzaninotto/Faker](https://github.com/fzaninotto/Faker), Alice
allows you to create a ton of fixtures/fake data for use while developing
or testing your project. It gives you a few essential tools to make it
very easy to generate complex data with constraints in a readable and easy
to edit way, so that everyone on your team can tweak the fixtures if needed.

**Warning**: this doc is being updated for alice 3.0. If you want to check the
documentation for 2.x, head [this way](https://github.com/nelmio/alice/tree/2.x).

## Table of Contents

1. [Installation](#installation)
1. [Example](#example)
1. [Getting Started](doc/getting-started.md)
  1. [Basic Usage](doc/getting-started.md#basic-usage)
  1. [Framework integration](doc/getting-started.md#framework-integration)
    1. [Symfony](doc/getting-started.md#symfony)
1. [Complete Reference](doc/complete-reference.md)
  1. [Creating Fixtures](doc/complete-reference.md#creating-fixtures)
    1. [YAML](doc/complete-reference.md#yaml)
    1. [PHP](doc/complete-reference.md#php)
  1. [Fixture Ranges](doc/complete-reference.md#fixture-ranges)
  1. [Calling Methods](doc/complete-reference.md#calling-methods)
  1. [Specifying Constructor Arguments](doc/complete-reference.md#specifying-constructor-arguments)
  1. [Using a factory](doc/complete-reference.md#using-a-factory)
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
  1. [Localized Fake Data](doc/customizing-data-generation.md#localized-fake-data) **TODO: port that change to v2**
  1. [Default Providers](doc/customizing-data-generation.md#default-providers)
    1. [Identity](doc/customizing-data-generation.md#identity)
  1. [Reuse generated data using objects value](doc/customizing-data-generation.md#reuse-generated-data-using-objects-value)
  1. [Custom Faker Data Providers](doc/customizing-data-generation.md#custom-faker-data-providers)
1. [Advanced Guide](doc/advanced-guide.md#advanced-guide)
  1. [Performance](doc/advanced-guide.md#performance)
  1. [Expression Language (DSL)](doc/advanced-guide.md#expression-language-dsl)
    1. [Parameters](doc/advanced-guide.md#parameters)
    1. [Functions](doc/advanced-guide.md#functions)
    1. [Identity](doc/advanced-guide.md#identity)
    1. [Arrays](doc/advanced-guide.md#arrays)
    1. [Optional](doc/advanced-guide.md#optional)
    1. [References](doc/advanced-guide.md#references)
    1. [Property Reference](doc/advanced-guide.md#property-reference)
  1. [Extending Alice](doc/advanced-guide.md#extending-alice)
    1. [Custom Flag](doc/advanced-guide.md#custom-flag)
    1. [Custom Instantiation](doc/advanced-guide.md#custom-instantiatior)
    1. [Custom Accessor](doc/advanced-guide.md#custom-accessor)
1. [Third-party libraries](#third-party-libraries)
1. [Contribute](#contribute)
  1. [Differences between 2.x and 3.x](#differences-between-2x-and-3x)
  1. [Architecture](#architecture)
    1. [FixtureBuilder](#fixturebuilder)
    1. [Generator](#generator)
  1. [Expression Language](#expression-language)
  1. [Contributing](#contributing)
    1. [Testing](#testing)
    1. [Profiling](#profiling))
1. [Upgrade](#upgrade)
1. [License](#license)

Other references:
  - [Tutorial: Using Alice in Symfony](https://knpuniversity.com/screencast/symfony-doctrine/fixtures-alice)

## Installation

This is installable via [Composer](https://getcomposer.org/) as
[nelmio/alice](https://packagist.org/packages/nelmio/alice):

    composer require --dev nelmio/alice:^3.0@beta


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
$loader = new Nelmio\Alice\Loader\NativeLoader();
$objectSet = $loader->loadFile(__DIR__.'/fixtures.yml');
```

Or load an array right away:

```php
$loader = new Nelmio\Alice\Loader\NativeLoader();
$objectSet = $loader->loadData([
    \Nelmio\Entity\User::class => [
        'user{1..10}' => [
            'username' => '<username()>',
            'fullname' => '<firstName()> <lastName()>',
            'birthDate' => '<date()>',
            'email' => '<email()>',
            'favoriteNumber' => '50%? <numberBetween(1, 200)>',
        ],
    ],
    \Nelmio\Entity\Group::class => [
        'group1' => [
            'name' => Admins,
            'owner' => '@user1',
            'members' => '<numberBetween(1, 10)>x @user*',
            'created' => '<dateTimeBetween("-200 days", "now")>',
            'updated' => '<dateTimeBetween($created, "now")>',
        ],
    ],
]);
```

For more information, refer to [the documentation](#table-of-contents).


## Third-party libraries

* Symfony:
  * [hautelook/AliceBundle](https://github.com/hautelook/AliceBundle)
  * [h4cc/AliceFixturesBundle](https://github.com/h4cc/AliceFixturesBundle)
  * [knplabs/rad-fixtures-load](https://github.com/KnpLabs/rad-fixtures-load)
* Nette
  * [Zenify/DoctrineFixtures](https://github.com/Zenify/DoctrineFixtures)
* Zend Framework 2:
  * [ma-si/aist-alice-fixtures](https://github.com/ma-si/aist-alice-fixtures)
* Framework Agnostic
  * [trappar/AliceGenerator](https://github.com/trappar/AliceGenerator)


## Contribute

Check the [contribution guide](CONTRIBUTING.md).


## Upgrade

Check the [upgrade guide](UPGRADE.md).


## License

Released under the [MIT License](LICENSE).
