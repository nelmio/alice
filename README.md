Alice - Expressive fixtures generator [![Build Status](https://secure.travis-ci.org/nelmio/alice.png?branch=master)](http://travis-ci.org/nelmio/alice)
=====================================

Alice allows you to create a ton of fixtures/fake data for use while
developing or testing your project. It gives you a few essential tools to
make it very easy to generate complex data with constraints in a readable
and easy to edit way, so that everyone on your team can tweak the fixtures
if needed.

## Installation ##

This is installable via [Composer](https://getcomposer.org/) as [nelmio/alice](https://packagist.org/packages/nelmio/alice):

    composer require nelmio/alice

To use it in Symfony2 you may want to use the [hautelook/alice-bundle](https://github.com/hautelook/AliceBundle), [h4cc/alice-fixtures-bundle](https://github.com/h4cc/AliceFixturesBundle) or [knplabs/rad-fixtures-load](https://github.com/KnpLabs/rad-fixtures-load) package instead.

## Table of Contents

- [Usage](#usage)
  - [Basic Usage](#basic-usage)
  - [Detailed Usage](#detailed-usage)
- [Reference](#reference)
  - [Creating Fixtures](#creating-fixtures)
  - [Fixture Ranges](#fixture-ranges)
  - [Faker Data](#faker-data)
  - [Calling Methods](#calling-methods)
  - [Specifying Constructor Arguments](#specifying-constructor-arguments)
  - [Optional Data](#optional-data)
  - [References](#references)
  - [Multiple References](#multiple-references)
  - [Handling Unique Constraints](#handling-unique-constraints)
  - [Fixture Inheritance](#fixture-inheritance)
  - [Including files](#including-files)
  - [Variables](#variables)
  - [Parameters](#parameters)
  - [Value Objects](#value-objects)
  - [Custom Faker Data Providers](#custom-faker-data-providers)
  - [Custom Setter](#custom-setter)
  - [Complete Sample](#complete-sample)
  - [Processors](#processors)

## Usage ##

### Basic Usage ###

The easiest way to use this is to call the static `Nelmio\Alice\Fixtures::load`
method. It will bootstrap everything for you and return you a set of persisted
objects in the container you give it.

Examples:

```php
// load a yaml file into a Doctrine\Common\Persistence\ObjectManager object
$objects = \Nelmio\Alice\Fixtures::load(__DIR__.'/fixtures.yml', $objectManager);

// load a php file into a Doctrine\Common\Persistence\ObjectManager object
$objects = \Nelmio\Alice\Fixtures::load(__DIR__.'/fixtures.php', $objectManager);
```

Note: You can also pass an array of filenames if you have multiple files with
references spanning more than one.

#### Options

`Fixtures::load` accepts a third `$options` argument that is an array
with the following keys:

- locale: the default locale
- providers: an array of additional Faker providers
- seed: a seed to make sure Faker generates data consistently across runs, set
  to null to disable (defaults to 1)
- logger: a callable or `Psr\Log\LoggerInterface` object that will receive progress
  information during the loading of the fixtures
- persist_once: only persist objects once if multiple files are passed, by default
  objects are persisted after each file

### Detailed Usage ###

If you want a bit more control you can instantiate the various object yourself
and make it work just as easily:

```php
// load objects from a yaml file
$loader = new \Nelmio\Alice\Fixtures\Loader();
$objects = $loader->load(__DIR__.'/fixtures.yml');

// optionally persist them into the doctrine object manager
// you can also do that yourself or persist them in another way
// if you do not use doctrine
$persister = new \Nelmio\Alice\Persister\Doctrine($objectManager);
$persister->persist($objects);
```

This loader maintains its list of built objects, so `load` can be called multiple times with different files if your fixture file starts growing unmanageably large.

Using the `Loader` class directly also allows you to add more customization to how your objects are instantiated, properties are set, and what kinds of files you can parse. The following methods are all available for these purposes:

* `addParser`: Parsers handle new types of files
* `addProcessor`: Processors handle new ways to generate properties
* `addBuilder`: Builders handle the generation of fixtures themselves
* `addInstantiator`: Instantiators handle creating instances
* `addPopulator`: Populators handle setting properties on instances

> **Note**: To load plain PHP files, the files must return an array containing the same structure as the yaml files have.

## Reference ##

### Creating Fixtures ###

The most basic functionality of this library is to turn flat yaml files into
objects. You can define many objects of different classes in one file as such:

```yaml
Nelmio\Entity\User:
    user0:
        username: bob
        fullname: Bob
        birthDate: 1980-10-10
        email: bob@example.org
        favoriteNumber: 42
    user1:
        username: alice
        fullname: Alice
        birthDate: 1978-07-12
        email: alice@example.org
        favoriteNumber: 27

Nelmio\Entity\Group:
    group1:
        name: Admins
```

This works fine, but it is not very powerful and is completely static. You
still have to do most of the work. Let's see how to make this more interesting.

### Fixture Ranges ###

The first step is to let Alice create many copies of an object for you
to remove duplication from the yaml file.

You can do that by defining a range in the fixture name:

```yaml
Nelmio\Entity\User:
    user{1..10}:
        username: bob
        fullname: Bob
        birthDate: 1980-10-10
        email: bob@example.org
        favoriteNumber: 42
```

Now it will generate ten users, with names user1 to user10. Pretty good but
we only have 10 bobs with the same name, username and email, which is not
so fancy yet.

You can also specify a list of values instead of a range:

```yaml
Nelmio\Entity\User:
    user{alice, bob}:
        username: <current()>
        fullname: <current()>
        birthDate: 1980-10-10
        email: <current()>@example.org
        favoriteNumber: 42
```

To go further we can just randomize data.

### Faker Data ###

Alice integrates with the [Faker](https://github.com/fzaninotto/Faker) library.
Using `<foo()>` you can call Faker data providers to generate random data. Check
the [list of Faker providers](https://github.com/fzaninotto/Faker#formatters).

Let's turn our static bob user into a randomized entry:

```yaml
Nelmio\Entity\User:
    user{1..10}:
        username: <username()>
        fullname: <firstName()> <lastName()>
        birthDate: <date()>
        email: <email()>
        favoriteNumber: <numberBetween(1, 200)>
```

As you see in the last line, you can also pass arguments to those just as if
you were calling a function.

To pass Faker Data to another Faker provider, you can use the `$fake()` closure
within faker calls. For example use `$fake('firstName', 'de_DE')` or
`$fake('numberBetween', null, 1, 200)` to call Faker. Pass the provider to call
followed by the locale (or null) and then the arguments to the provider. Here
is a detailed yaml example.

```yaml
Nelmio\Entity\User:
    user{1..10}:
        username: User<identity($fake('numberBetween', 1, 100) / 2 + 5)>
```

In plain PHP fixtures the `$fake` closure is also available.

#### Localized Fake Data ####

Faker can create localized data for addresses, phone numbers and so on. You can
set the default locale to use by passing a `locale` value in the `$options`
array of `Fixtures::load`.

Additionally, you can mix locales by adding a locale prefix to the faker key,
i.e. `<fr_FR:phoneNumber()>` or `<de_DE:firstName()>`.

#### Default Providers ####

Alice includes a default identity provider, `<identity()>`, that
simply returns whatever is passed to it. This allows you among other
things to use a PHP expression while still benefitting from
[variable replacement](#variables). This is similar to an `eval()`
call, allowing you to do things like math or similar, e.g.
`<identity(1 + $favoriteNumber)>`.

Some syntactic sugar is provided for this as well, and `<($whatever)>`
is an alias for `<identity($whatever)>`.

### Calling Methods ###

Sometimes though you need to call a method to initialize some more data, you
can do this just like with properties but instead using the method name and
giving it an array of arguments. For example let's assume the user class has
a `setLocation` method that requires a latitude and a longitude:

```yaml
Nelmio\Entity\User:
    user1:
        username: <username()>
        setLocation: [40.689269, -74.044737]
```

### Specifying Constructor Arguments ###

When a constructor has mandatory arguments you must define it as explained
above, for example if the User required a username in the constructor you
could do the following:

```yaml
Nelmio\Entity\User:
    user1:
        __construct: [<username()>]
```

If you want to call a static factory method instead of a constructor, you can
specify a hash as the constructor:

```yaml
Nelmio\Entity\User:
    user1:
        __construct: { create: [<username()>] }
```

If you specify `false` in place of constructor arguments, Alice will
instantiate the object without executing the constructor:

```yaml
Nelmio\Entity\User:
    user1:
        __construct: false
```

Note: If you are using a private constructor without any mandatory arguments you can omit the constructor altogether.
Private constructors with mandatory arguments should use the static factory method described above.

### Optional Data ###

Some fields do not have to be filled-in, like the `favoriteNumber` in this
example might be personal data you don't want to share, to reflect this in
our fixtures and be sure the site works and looks alright even when users
don't enter a favorite number, we can make Alice fill it in *sometimes* using
the `50%? value : empty value` notation. It's a bit like the ternary operator,
and you can omit the empty value if null is ok as such: `50%? value`.

Let's update the user definition with this new information:

```yaml
Nelmio\Entity\User:
    user{1..10}:
        username: <username()>
        fullname: <firstName()> <lastName()>
        birthDate: <date()>
        email: <email()>
        favoriteNumber: 50%? <numberBetween(1, 200)>
```

Now only half of the users will have a number filled-in.

### References ###

Let's get back to the Group. Ideally a group should have members, and Alice
allows you to reference one object from another one. You can do that with the
`@name` notation, where name is a fixture name from any class.

Let's add a fixed owner to the group:

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: @user1
```

Alice also allows you to directly reference objects' properties using the ```@name->property``` notation.

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: @user1->username
```

To be able to use this feature, your entities have to match some requirements :
* You can reference public properties
* You can reference properties reachable through a getter (i.e : ```@name->property``` will call ```$name->getProperty()``` if ```property``` is not public)
* You can reference entities' ID but you will then have to split fixtures in multiple files (this is because objects are persisted at the end of each file processing) :

```yaml
# fixture_user.yml
Nelmio\Entity\User:
    # ...
```

```yaml
# fixture_group.yml
Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: @user1->id
```

If you want to create ten users and ten groups and have each user own one
group, you can use `<current()>` which is replaced with the current id of
each iteration when using fixture ranges:

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group{1..10}:
        owner: @user<current()>
```

If you would like a random user instead of a fixed one, you can define a
reference with a wildcard:

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: @user*
```

It will then pick any object whose name matches `user*` where `*` can be any
string.

There is one limitation, you can only refer to objects that are defined above
in the file. If you want to use an existing object that is already present in
your database you can also provide the id of the object. For this to work
however the setter method for that property must have a type hint.

```yaml
Nelmio\Entity\Group:
    group1:
        owner: 1 # this will try to fetch the User (as typehinted in Group::setOwner) with id 1
```

It is also possible to create a relation to a random object by id:

```yaml
Nelmio\Entity\Group:
    group1:
        owner: <numberBetween(1, 200)>
```

> **Note**: To create a string `@foo` that is not a reference you can escape it
> as `\@foo`

### Multiple References ###

If we also want to add group members, there are two ways to do this.
One is to define an array of references to have a fixed set of members:

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: @user1
        members: [@user2, @user3]
```

Another, which is more interesting, is to define a reference with a wildcard,
and also tell Alice how many object you want:

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: @user1
        members: 5x @user*
```

In this case it will pick 5 fixture objects which have a name matching `user*`.

You can also randomize the amount by combining it with faker data:

```yaml
    # ...
        members: <numberBetween(1, 10)>x @user*
```

If the data needs to be static instead, you can use the same syntax as
the one used for fixtures range.

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group{1..10}:
        members: @user{1..10}
```

> **Note**: You do not need to define multi-references inside an array, since
> they are automatically translated to an array of objects.

#### Self reference ####

The `@self` reference is assigned to the current fixture instance.

#### Passing references to providers ####

You can pass references to providers much like you can pass [variables](#variables):

```yaml
Nelmio\Entity\Group:
    group1:
        owner: <numberBetween(1, 200)>
    group2:
        owner: <numberBetween(@group1->owner, 200)>
```
### Handling Unique Constraints ###

Quite often some database fields have a unique constraint set on them, in which
case having the fixtures randomly failing to generate because of bad luck is
quite annoying. This is especially important if you generate large amounts of
objects, as otherwise you will most likely never encounter this issue.

By declaring the key as unique using the `(unique)` flag at the end, Alice
will make sure every element of this class that is created has a unique value
for that property. For example:

```yaml
Nelmio\Entity\User:
    user{1..10}:
        username (unique): <username()>
```

### Fixture inheritance ###

Base fixtures, to be extended from, can be created to be able to *only* need
to define less additional values in a set of common fixture definitions.

By declaring a fixture as a template using the `(template)` flag, Alice will set
the instance as a template for that file. Template instances are not persisted.

Templates can also make use of inheritance themselves, by extending from other
templates, allowing you to create, mix and match templates. For example:

```yaml
Nelmio\Entity\User:
    user_bare (template):
        username: <username()>
    user_full (template, extends user_bare):
        name: <firstName()>
        lastname: <lastName()>
        city: <city()>
```

Templates can be extended by other fixtures making use of the `(extends)` flag
followed by the name of the template to extend.

```yaml
Nelmio\Entity\User:
    user (template):
        username: <username()>
        age: <numberBetween(1, 20)>
    user1 (extends user):
        name: <firstName()>
        lastname: <lastName()>
        city: <city()>
        age: <numberBetween(1, 50)>
```

Inheritance also allows to extend from several templates. The last declared `extends`
will always override values from previous declared `extends` templates. However,
extension properties will never override values set explicitly in the fixture spec
itself.

In the following example, the age from `user_young` will override the age from `user`
in `user1`, while username will remain `user1`:

```yaml
Nelmio\Entity\User:
    user (template):
        username: <username()>
        age: <numberBetween(1, 40)>
    user_young (template):
        age: <numberBetween(1, 20)>
    user1 (extends user, extends user_young):
        username: user1
        name: <firstName()>
        lastname: <lastName()>
        city: <city()>
```

### Including files ###

You may include other files from your fixtures using the top-level `include` key:

```yaml
include:
    - relative/path/to/file.yml
    - relative/path/to/another/file.yml
Nelmio\Entity\User:
    user1 (extends user, extends user_young):
        name: <firstName()>
        lastname: <lastName()>
        city: <city()>
```

In `relative/path/to/file.yml`:

```yaml
Nelmio\Entity\User:
    user (template):
        username: <username()>
        age: <numberBetween(1, 40)>
```

In `relative/path/to/another/file.yml`:

```yaml
Nelmio\Entity\User:
    user_young (template):
        age: <numberBetween(1, 20)>
```

All files are merged in one data set before generation, and the includer's content
takes precedence over included files' fixtures in case of duplicate keys.

### Variables ###

For some advanced use cases you sometimes need to reference one property
from another, for example to generate the update date while making sure
it is *after* the creation date. If you simply use two random dates it might
be that they are reversed, but Alice let's you refer to other properties
using the traditional PHP `$variable` notation.

Let's add created/modified dates to our group:

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: @user1
        members: <numberBetween(1, 10)>x @user*
        created: <dateTimeBetween('-200 days', 'now')>
        updated: <dateTimeBetween($created, 'now')>
```

As you can see, we make sure that the update date is between the creation
date and the current time, which ensure the data will look real enough.

### Parameters ###

When using the Yaml loader, you can also set global parameters that will be inserted everywhere those values are used to help with readability. For example:

```yaml
parameters:
  ebay_domain_name: ebay.us

Nelmio\Entity\Shop:
  shop1:
    domain: <{ebay_domain_name}>
```

Additionally, you can pass in a list of defined parameters as the last argument to the `Loader` class to prepare the initial set of values.

### Value Objects ###

Sometimes you require value objects that are not persisted by an ORM, but
are just stored on other objects. You can use the `(local)` flag on the class
or the instance name to mark them as non-persistable. They will be available
as references to use in other objects, but will not be returned by the
`LoaderInterface::load` call.

For example this avoids getting an error because Geopoint is not an Entity
if you use the Doctrine persister.

```yaml
Nelmio\Data\Geopoint (local):
    geo1:
        __construct: [<latitude()>, <longitude()>]

Nelmio\Entity\Location:
    loc{1..100}:
        name: <city()>
        geopoint: @geo1
```

### Custom Faker Data Providers ###

Sometimes you need more than what Faker and Alice provide you natively, and
there are three ways to solve the problem:

#### Embed PHP code in the yaml file

It is included by the loader so you can add arbitrary PHP as long as it outputs
valid yaml. That said, this is like PHP templates, it quickly ends up very messy
if you do too much logic, so it's best to extract logic out of the templates.
  
#### Public method in the [`Nelmio\Alice\Fixtures\Loader`](src/Nelmio/Alice/Fixtures/Loader.php)

All the public methods are available as `<method()>` in the Alice fixture files.
For example if you want a custom group name generator and you use the standard
Doctrine Fixtures package in a Symfony2 project, you could do the following:

```php
<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Nelmio\Alice\Fixtures;

class LoadFixtureData implements FixtureInterface
{
   public function load(ObjectManager $om)
   {
       // pass $this as an additional faker provider to make the "groupName"
       // method available as a data provider
       Fixtures::load(__DIR__.'/fixtures.yml', $om, array('providers' => array($this)));
   }

   public function groupName()
   {
       $names = array(
           'Group A',
           'Group B',
           'Group C',
       );

       return $names[array_rand($names)];
   }
}
```

That way you can now use `name: <groupName()>` to generate specific group names.
   
#### Add a custom [Faker Provider](https://github.com/fzaninotto/Faker/tree/master/src/Faker/Provider) class

```php
<?php

namespace AppBundle\DataFixtures\ORM;

use Faker\Provider\Base as BaseProvider;

class JobProvider extends BaseProvider
{
   /**
    * Sources: {@link http://siliconvalleyjobtitlegenerator.tumblr.com/}
    *
    * @var array List of job titles.
    */
   private $titleProvider = [
       'firstname' => [
           'Audience Recognition',
           'Big Data',
           'Bitcoin',
           '...',
           'Video Experience',
           'Wearables',
           'Webinar',
       ],
       'lastname' => [
           'Advocate',
           'Amplifier',
           'Architect',
           '...',
           'Warlock',
           'Watchman',
           'Wizard',
       ],
       'fullname' => [
           'Conductor of Datafication',
           'Crowd-Funder-in-Residence',
           'Quantified-Self-in-Residence',
           '...',
           'Tech-Svengali-in-Residence',
           'Tech-Wizard-in-Residence',
           'Thought-Leader-in-Residence',
       ],
   ];

   /**
    * Sources: {@link http://sos.uhrs.indiana.edu/Job_Code_Title_Abbreviation_List.htm}
    *
    * @var array List of job abbreviations.
    */
   private $abbreviationProvider = [
       'ABATE',
       'ACAD',
       'ACCT',
       '...',
       'WCTR',
       'WSTRN',
       'WKR',
   ];


   /**
    * @return string Random job title.
    */
   public function jobTitle()
   {
       $names = [
           sprintf(
               '%s %s',
               self::randomElement($this->titleProvider['firstname']),
               self::randomElement($this->titleProvider['lastname'])
           ),
           self::randomElement($this->titleProvider['fullname']),
       ];
       return self::randomElement($names);
   }
   /**
    * @return string Random job abbreviation title
    */
   public function jobAbbreviation()
   {
       return self::randomElement($this->abbreviationProvider);
   }
}
```

You will need to inject a Faker generator instance, which you can get thanks to [`Nelmio\Alice\Instances\Processor\Methods\Faker`](src/Nelmio/Alice/Instances/Processor/Methods/Faker.php).

Then, inject your provider to the [`Nelmio\Alice\Fixtures\Loader`](src/Nelmio/Alice/Fixtures/Loader.php).

### Custom Setter ###

In case, you want to specify a custom function that will be used to set all the values,
you can specify a `__set` value:

```yaml
Nelmio\Data\Geopoint:
    geo1:
        __set: customSetter
        foo: bar
```

When the objects are populated, the `customSetter` function will be called, with the first parameter
being the `key`, the second one being the `value` (so similar to the magic PHP setter). In the above
example, the following call will be made on the instance when populating:

```php
$geopoint->customSetter('foo', 'bar');
```

### Complete Sample ###

In the end, using most of the tools above, we have this file creating a bunch
of users and a group, all of it being linked together, and with little typing:

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
        owner: @user1
        members: <numberBetween(1, 10)>x @user*
        created: <dateTimeBetween('-200 days', 'now')>
        updated: <dateTimeBetween($created, 'now')>
```

If you like to have a few specific users with specific data to write tests
against of course you can define them above/below the ones using the randomized
data. Combine it all as you see fit!

### Processors ###

Processors allow you to process objects before and/or after they are persisted. Processors
must implement the `ProcessorInterface`.

Here is an example where we may use this feature to make sure passwords are properly
hashed on a `User`:

```php
namespace Acme\DemoBundle\DataFixtures\ORM;

use Nelmio\Alice\ProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use User;

class UserProcessor implements ProcessorInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function preProcess($object)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function postProcess($object)
    {
        if (!($object instanceof User)) {
            return;
        }

        $hasher = $this->container->get('example_password_hasher');
        $object->password = $hasher->hash($object->password);
    }
}
```
You can add a list of processors in the load method, e.g.

```php
$objects = \Nelmio\Alice\Fixtures::load(__DIR__.'/fixtures.yml', $objectManager, $options, $processors);
```

Or, you can add them to your loader using the `addProcessor()` method, e.g.

```php
$loader = new \Nelmio\Alice\Fixtures($objectManager, $options);
$loader->addProcessor($processor);
$objects = $loader->loadFiles(__DIR__.'/fixtures.yml');
```

## License ##

Released under the MIT License, see LICENSE.
