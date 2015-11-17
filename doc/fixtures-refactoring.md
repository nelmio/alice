# Keep Your Fixtures Dry

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


## Including files

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
        members: <numberBetween(1, 10)>x @user*
        created: <dateTimeBetween('-200 days', 'now')>
        updated: <dateTimeBetween($created, 'now')>
```

As you can see, we make sure that the update date is between the creation
date and the current time, which ensure the data will look real enough.


## Parameters

When using the Yaml loader, you can also set global parameters that will be inserted everywhere those values are used to help with readability. For example:

```yaml
parameters:
  ebay_domain_name: ebay.us

Nelmio\Entity\Shop:
  shop1:
    domain: <{ebay_domain_name}>
```

Additionally, you can pass in a list of defined parameters as the last argument to the `Loader` class to prepare the initial set of values.


Next chapter: [Customize Data Generation](customizing-data-generation.md)<br />
Previous chapter: [Handling Relations](relations-handling.md)
