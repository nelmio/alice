Alice - Expressive fixtures generator [![Build Status](https://secure.travis-ci.org/nelmio/alice.png?branch=master)](http://travis-ci.org/nelmio/alice)
=====================================

Relying on [fzaninotto/Faker](https://github.com/fzaninotto/Faker), Alice
allows you to create a ton of fixtures/fake data for use while developing
or testing your project. It gives you a few essential tools to make it
very easy to generate complex data with constraints in a readable and easy
to edit way, so that everyone on your team can tweak the fixtures if needed.


## Table of Contents

1. [Installation](#installation)
1. [Example](#example)
1. [Getting Started](doc/getting-started.md)
  1. [Basic Usage](doc/getting-started.md#basic-usage)
  2. [Detailed Usage](doc/getting-started.md#detailed-usage)
1. [Complete Reference](doc/complete-reference.md)
  3. [Creating Fixtures](doc/complete-reference.md#creating-fixtures)
  4. [Fixture Ranges](doc/complete-reference.md#fixture-ranges)
  1. [Calling Methods](doc/complete-reference.md#calling-methods)
  1. [Specifying Constructor Arguments](doc/complete-reference.md#specifying-constructor-arguments)
  1. [Custom Setter](doc/complete-reference.md#custom-setter)
  1. [Optional Data](doc/complete-reference.md#optional-data)
  1. [Handling Unique Constraints](doc/complete-reference.md#handling-unique-constraints)
1. [Handling Relations](doc/relations-handling.md)
  1. [References](doc/relations-handling.md#references)
  2. [Multiple References](doc/relations-handling.md#multiple-references)
  3. [Self reference](doc/relations-handling.md#self-reference)
  4. [Passing references to providers](doc/relations-handling.md#passing-references-to-providers)
1. [Keep Your Fixtures Dry](doc/fixtures-refactoring.md)
  1. [Fixture Inheritance](doc/fixtures-refactoring.md#fixture-inheritance)
  2. [Including files](doc/fixtures-refactoring.md#including-files)
  3. [Variables](doc/fixtures-refactoring.md#variables)
  4. [Parameters](doc/fixtures-refactoring.md#parameters)
1. [Customize Data Generation](doc/customizing-data-generation.md)
  1. [Faker Data](doc/customizing-data-generation.md#faker-data)
  2. [Reuse generated data using objects value](doc/customizing-data-generation.md#reuse-generated-data-using-objects-value) 
  3. [Custom Faker Data Providers](doc/customizing-data-generation.md#custom-faker-data-providers)
20. [Event handling with Processors](doc/processors.md)


## Installation

This is installable via [Composer](https://getcomposer.org/) as [nelmio/alice](https://packagist.org/packages/nelmio/alice):

    composer require nelmio/alice


## Example

Here is a complete example of entity declaration:

```yaml
Nelmio\Entity\User:
    user{1..10}:
        username: <username()>
        fullname: <firstName()> <lastName()>
        birthDate: <date()>
        email: <email()>
        favoriteNumber: 50%? <numberBetween(1, 200)>

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: '@user1'
        members: <numberBetween(1, 10)>x @user*
        created: <dateTimeBetween('-200 days', 'now')>
        updated: <dateTimeBetween($created, 'now')>
```

You can then load them easily with:

```php
$objects = \Nelmio\Alice\Fixtures::load(__DIR__.'/fixtures.yml', $objectManager);
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


## License

Released under the [MIT License](LICENSE).
