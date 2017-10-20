# Handling Relations

1. [References](#references)
1. [Multiple References](#multiple-references)
1. [Self reference](#self-reference)
1. [Passing references to providers](#passing-references-to-providers)


## References

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
        owner: '@user1'
```

**Warning:** References (e.g. here `group1`) should always be composed of
letters, digits, periods (`.`), underscores (`_`) and slashes (`/`). Other
characters such as `{`, `}`, `(`, `)` are still allowed but hold a special
meaning (e.g. for [ranged fixtures](complete-reference.md#fixture-ranges)).

Alice also allows you to directly reference objects' properties using
the `@name->property` notation or calling an object method
`@name->getProperty()`.

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: '@user1->username'
```

To be able to use this feature, your entities have to match some requirements :
* You can reference public properties
* You can reference properties reachable through a getter (i.e :
`@name->property` will call `$name->getProperty()` if ```property``` is not
public)
* You can reference private properties [by decorating the property accessor with the `ReflectionPropertyAccessor`](advanced-guide.md#custom-accessor)
* You can reference entities' ID :

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
        owner: '@user1->id'
```

**Warning:** If you are using IDs this way, this either means you are setting the IDs when your object is created like
with UUIDs or you will not have any guarantee the ID is not already used in the database.

If you want to create ten users and ten groups and have each user own one
group, you can use `<current()>` which is replaced with the current ID of
each iteration when using fixture ranges:

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group{1..10}:
        owner: '@user<current()>'
```

If you would like a random user instead of a fixed one, you can define a
reference with a wildcard:

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: '@user*'
```

It will then pick any object whose name matches `user*` where `*` can be any
string.

It is also possible to create a relation to a random object by id:

```yaml
Nelmio\Entity\Group:
    group1:
        owner: '@user<numberBetween(1, 200)>'
```

> **Note**: To create a string `@foo` that is not a reference you can escape it
> as `\@foo`

> **Note**: When `@` is used in the middle of a word, e.g. `email@example.com`,
it will be automatically escaped.


## Multiple References

If we also want to add group members, there are two ways to do this.
One is to define an array of references to have a fixed set of members:

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: '@user1'
        members: ['@user2', '@user3']
```

Another, which is more interesting, is to define a reference with a wildcard,
and also tell Alice how many objects you want:

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: '@user1'
        members: '5x @user*'
```

In this case it will pick 5 fixture objects which have a name matching `user*`.

You can also randomize the amount by combining it with faker data:

```yaml
    # ...
        members: '<numberBetween(1, 10)>x @user*'
```

If the data needs to be static instead, you can use the same syntax as
the one used for fixtures range.

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group{1..10}:
        members: '@user{1..10}'
```

> **Note**: You do not need to define multi-references inside an array, since
> they are automatically translated to an array of objects.


## Self reference

The `@self` reference is assigned to the current fixture instance.


## Passing references to providers

You can pass references to providers much like you can pass [variables](fixtures-refactoring.md#variables):

```yaml
Nelmio\Entity\Group:
    group1:
        owner: '<numberBetween(1, 200)>'

    group2:
        owner: '<numberBetween(@group1->owner, 200)>'
```


<br />
<hr />

« [Keep Your Fixtures Dry](fixtures-refactoring.md) • [Complete Reference](complete-reference.md) »
