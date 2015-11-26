# Handling Relations

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

Alice also allows you to directly reference objects' properties using the ```@name->property``` notation.

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
        owner: '@user1->id'
```

If you want to create ten users and ten groups and have each user own one
group, you can use `<current()>` which is replaced with the current id of
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
and also tell Alice how many object you want:

```yaml
Nelmio\Entity\User:
    # ...

Nelmio\Entity\Group:
    group1:
        name: Admins
        owner: '@user1'
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
        members: '@user{1..10}'
```

> **Note**: You do not need to define multi-references inside an array, since
> they are automatically translated to an array of objects.

## Self reference

The `@self` reference is assigned to the current fixture instance.

## Passing references to providers

You can pass references to providers much like you can pass [variables](#variables):

```yaml
Nelmio\Entity\Group:
    group1:
        owner: <numberBetween(1, 200)>
    group2:
        owner: <numberBetween(@group1->owner, 200)>
```

Next chapter: [Keep Your Fixtures Dry](fixtures-refactoring.md)<br />
Previous chapter: [Complete Reference](complete-reference.md)
