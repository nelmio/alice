# Complete Reference

## Creating Fixtures

### YAML

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

### PHP

You can also specify fixtures in PHP by returing an array where each key with the following structure:

```php
<?php

return [
    'Nelmio\Alice\support\models\User' => [
        'user1' => [
            'username' => '<identity($fake("upperCaseProvider", null, "John Doe"))>',
            'fullname' => '<upperCaseProvider("John Doe")>',
        ],
        'user2' => [
            'username' => $fake('identity', null, $fake('upperCaseProvider', null, 'John Doe')),
            'fullname' => $fake('upperCaseProvider', null, 'John Doe'),
        ],
    ],
];
```

## Fixture Ranges

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


## Calling Methods

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


## Specifying Constructor Arguments

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


## Custom Setter

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


## Optional Data

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


## Handling Unique Constraints

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

Next chapter: [Handling Relations](relations-handling.md)<br />
Previous chapter: [Getting Started](getting-started.md)
