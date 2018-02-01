# Keep Your Fixtures Dry

1. [Fixture Inheritance](#fixture-inheritance)
1. [Including files](#including-files)
1. [Variables](#variables)
1. [Parameters](#parameters)
    1. [Static parameters](#static-parameters)
    1. [Dynamic parameters](#dynamic-parameters)
    1. [Composite parameters](#composite-parameters)
    1. [Usage with functions (constructor included)](#usage-with-functions-constructor-included)
    1. [Inject external parameters](#inject-external-parameters)


## Fixture inheritance

Base fixtures, to be extended from, can be created to be able to *only* need
to define less additional values in a set of common fixture definitions.

By declaring a fixture as a template using the `(template)` flag, Alice will set
the instance as a template for that file. Template instances are not persisted.

Templates can also make use of inheritance themselves, by extending from other
templates, allowing you to create, mix and match templates. For example:

```yaml
Nelmio\Entity\User:
    user_bare (template):
        username: '<username()>'
    user_full (template, extends user_bare):
        name: '<firstName()>'
        lastname: '<lastName()>'
        city: '<city()>'
```

Templates can be extended by other fixtures making use of the `(extends)` flag
followed by the name of the template to extend.

```yaml
Nelmio\Entity\User:
    user (template):
        username: '<username()>'
        age: '<numberBetween(1, 20)>'

    user1 (extends user):
        name: '<firstName()>'
        lastname: '<lastName()>'
        city: '<city()>'
        age: '<numberBetween(1, 50)>'
```

Inheritance also allows to extend from several templates. The last declared
`extends` will always override values from previous declared `extends`
templates. However, extension properties will never override values set
explicitly in the fixture spec itself.

In the following example, the age from `user_young` will override the age from
`user` in `user1`, while username will remain `user1`:

```yaml
Nelmio\Entity\User:
    user (template):
        username: '<username()>'
        age: '<numberBetween(1, 40)>'

    user_young (template):
        age: '<numberBetween(1, 20)>'

    user1 (extends user, extends user_young):
        username: user1
        name: '<firstName()>'
        lastname: '<lastName()>'
        city: '<city()>'
```


## Including files

You may include other files from your fixtures using the top-level `include` key:

```yaml
include:
    - relative/path/to/file.yml
    - /absolute/path/to/another/file.yml

Nelmio\Entity\User:
    user1 (extends user, extends user_young):
        name: '<firstName()>'
        lastname: '<lastName()>'
        city: '<city()>'
```

In `relative/path/to/file.yml`:

```yaml
Nelmio\Entity\User:
    user (template):
        username: '<username()>'
        age: '<numberBetween(1, 40)>'
```

In `/absolute/path/to/another/file.yml`:

```yaml
Nelmio\Entity\User:
    user_young (template):
        age: '<numberBetween(1, 20)>'
```

All files are merged in one data set before generation, and the includer's
content takes precedence over included files' fixtures in case of duplicate keys.


## Variables

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
        owner: '@user1'
        members: '<numberBetween(1, 10)>x @user*'
        created: '<dateTimeBetween("-200 days", "now")>'
        updated: '<dateTimeBetween($created, "now")>'
```

As you can see, we make sure that the update date is between the creation
date and the current time, which ensure the data will look real enough.


## Parameters

When using the YAML loader, you can also set global parameters that will be
inserted everywhere those values are used to help with readability. For example:

### Static parameters

```yaml
parameters:
    ebay_domain_name: ebay.us

Nelmio\Entity\Shop:
    shop{1..10}:
        domain: '<{ebay_domain_name}>'
        # or
        domain: '<($ebay_domain_name)>'
```

**Note**: parameters are not evaluated in 3.x, meaning that the following:

```yaml
parameters:
    shop_id: '<uniqid()>'

Nelmio\Entity\Shop:
    shop{1..10}:
        id: '<{shop_id}>'
```

Will give you `'<{shop_id}>'` for the `id` of `shop1`, `shop2`, ... `shop10`. In 2.x, this was doable to some extend, if
you really need parameters that benefit from that feature in alice 3.x you can use the following workaround:

```yaml
stdClass
    parameters:
        shop_id: '<uniqid()>'

Nelmio\Entity\Shop:
    shop{1..10}:
        id: '@parameters->shop_id'
```

But then all the `shop*` instances will have the same ID, which may not be what you want. Instead you should call
directly `<uniqid()>` in the example above or user a custom Faker provider if the expression is too complex.



### Dynamic parameters 

```yaml
parameters:
    username_alice: Alice
    username_bob: Bob

Nelmio\Entity\User:
    user_{alice, bob}:
        username: '<{username_<current()>}>' # Will be 'Alice' for 'user_alice' and 'Bob' for 'user_bob'
```


### Composite parameters

```yaml
parameters:
    key1: NaN
    key2: Bat
    composite: '<{key1}> <{key2}>!'

Nelmio\Entity\User:
    user0:
        username: '<{composite}>' # 'NaN Bat!'
```


### Usage with functions (constructor included)

```yaml
parameters:
    foo: bar

Nelmio\Entity\Dummy:
    dummy{1..10}:
        __construct:
            arg0: '<{foo}>'
            arg1: '$arg0' # will be resolved info 'bar'
            3: 500  # the numerical key here is just a random number as in YAML you cannot mix keys with array values
            4: '$3' # `3` here refers to the *third* argument, i.e. 500
```

**Note**: as you can see, arguments can be used as parameters as you go. They however will only in the scope of that 
function, i.e. in the above the parameter `$arg0` is usable only within the `__construct` declaration above.

The case above can be a bit confusing in YAML, in PHP it would be the following:

```php
[
    'parameters' => [
        'foo' => 'bar',
    ],
    Nelmio\Entity\Dummy::class => [
        'dummy{1..10}' => [
            '__construct' => [
                'arg0' => '<{foo}>',
                'arg1' => '$arg0',
                500,
                '$3',
            ],
        ],
    ],
],
```


### Inject external parameters

You can pass in a list of defined parameters as the second
argument of `{File,Data}LoaderInterface::load{File,Data}()`.


<br />
<hr />

« [Customize Data Generation](customizing-data-generation.md) • [Handling Relations](relations-handling.md) »
