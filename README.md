Alice - Expressive fixtures generator [![Build Status](https://secure.travis-ci.org/nelmio/alice.png?branch=master)](http://travis-ci.org/nelmio/alice)
=====================================

Alice allows you to create a ton of fixtures/fake data for use while
developing or testing your project. It gives you a few essential tools to
make it very easy to generate complex data with constraints in a readable
and easy to edit way, so that everyone on your team can tweak the fixtures
if needed.

## Installation ##

This is installable via [Composer](https://getcomposer.org/) as [nelmio/alice](https://packagist.org/packages/nelmio/alice).

**BC Break Warning**: For compat with XML/HTML in fixtures, the round braces
are now enforced on faker calls, i.e. use `<foo()>` instead of `<foo>`. If
you don't have time to upgrade you can require
`"nelmio/alice": "1.0.x-dev#12423116eed"` in the meantime.

## Usage ##

### Basic Usage ###

The easiest way to use this is to call the static `Nelmio\Fixture\Fixture::load`
method. It will bootstrap everything for you and return you a set of objects
persister in the container you give it.

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

### Detailed Usage ###

If you want a bit more control you can instantiate the various object yourself
and make it work just as easily:

```php
// load objects from a yaml file
$loader = new \Nelmio\Alice\Loader\Yaml();
$objects = $loader->load(__DIR__.'/fixtures.yml');

// optionally persist them into the doctrine object manager
// you can also do that yourself or persist them in another way
// if you do not use doctrine
$persister = new \Nelmio\Alice\ORM\Doctrine($objectManager);
$persister->persist($objects);
```

> **Note**: To load plain PHP files, you can use the `\Nelmio\Alice\Loader\Base`
> class instead. These PHP files must return an array containing the same
> structure as the yaml files have.

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

#### Localized Fake Data ####

Faker can create localized data for adresses, phone numbers and so on. You can
set the default locale to use by passing a `locale` value in the `$options`
array of Fixtures::load.

Additionally, you can mix locales by adding a locale prefix to the faker key,
i.e. `<fr_FR:phoneNumber()>` or `<de_DE:firstName()>`.

### Optional Data ###

Some fields do not have to be filled-in, like the `favoriteNumber` in this
example might be personal data you don't want to share, to reflect this in
our fixtures and be sure the site works and looks alright even when users
don't enter a favorite number, we can make Alice fill it in *sometimes* using
the `50%? value : empty value` notation. It's a bit like the ternary operator,
and you can omit the empty value if null is ok as such: `%50? value`.

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

Now only half the user will have a number filled-in.

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

### Multiple References ###

If we want to also add group members, there are two ways to do this.
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

The other, which is more interesting, is to define a reference with a wildcard,
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

> **Note**: You do not need to define multi-references inside an array, since
> they are automatically translated to an array of objects.

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

### Custom Faker Data Providers ###

Sometimes you need more than what Faker and Alice provide you natively, and
there are two ways to solve the problem:

1. Embed PHP code in the yaml file. It is included by the loader so you can
   add arbitrary PHP as long as it outputs valid yaml. That said, this is like
   PHP templates, it quickly ends up very messy if you do too much logic, so
   it's best to extract logic out of the templates.
2. Add a custom Faker Provider class. These are just classes that expose public
   methods, all the public methods are available as `<method()>` in the Alice
   fixture files. For example if you want a custom group name generator and you
   use the standard Doctrine Fixtures package in a Symfony2 project, you could
   do the following:

   ```php
   <?php

   namespace Acme\DemoBundle\DataFixtures\ORM;

   use Doctrine\Common\Persistence\ObjectManager;
   use Doctrine\Common\DataFixtures\FixtureInterface;
   use Nelmio\Alice\Fixtures;

   class LoadFixtureData implements FixtureInterface
   {
       public function load(ObjectManager $om)
       {
           // pass $this as an additional faker provider to make the "groupName"
           // method available as a data provider
           Fixture::load(__DIR__.'/fixtures.yml', $om, array('providers' => array($this)));
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

### Complete Sample ###

In the end, using all the tools above, we have this file creating a bunch of
users and a group, all of it being linked together, and with little typing:

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

## License ##

Released under the MIT License, see LICENSE.
