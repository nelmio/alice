# Contribution Guide

This guide is for those who wants to have a better understand on the library internals and how to get started to
contribute. The code and the tests are the best documentation you will find, this guide aims at introducing the higher
picture of the library mechanisms only.

1. [Differences between 2.x and 3.x](#differences-between-2x-and-3x)
1. [Architecture](#architecture)
    1. [FixtureBuilder](#fixturebuilder)
    1. [Generator](#generator)
1. [Expression Language](#expression-language)
1. [Contributing](#contributing)


## Differences between 2.x and 3.x

Main differences between 2.x and 3.x:

- The persistence layer has been removed
- Rewrite of the API to make it more extensible and more robust
- Change in the architecture to address some limitations found in 2.x


## Architecture

The two entry points of the library are the `DataLoader` and `FileLoader`:

![FileLoader](doc/img/FileLoader.png)

![DataLoader](doc/img/DataLoader.png)

Alice problematic is to render an array PHP containing a description of objects and parameters with external objects
and parameters that can be injected, into a set of parameters and objects called `ObjectSet.

In `DataLoader`, you can start to see the two second biggest components:

- `FixtureBuilder`: responsible to denormalize the PHP array given into a comprehensible set of data called `FixtureSet`
- `Generator`: responsible to generate the objects and resolve the parameters described in `FixtureSet` to generate an `ObjectSet`


### FixtureBuilder

`FixtureBuilder` is composed of two components: `Denormalizer` and `ExpressionLanguage`. The first one is in charged of
transforming the input data array with the injected values in a collection of `Fixture` which describes an object to
generate and parameters.

`ExpressionLanguage` is the component used by the `Denormalizer` to parse the values such as `@user<current()>`, i.e.
interpret alice DSL.

The result `FixtureSet` is always invariant: reloading the same set of data will always result in the same result, hence
is cacheable.


### Generator

The `Generator` is responsible to transform a `FixtureSet` which is composed of `Fixture`, parameters, injected objects
injected parameters into an `ObjectSet` which is a collection of PHP objects and parameters. Because some data are
generated "randomly" thanks to [Faker][1], a `FixtureSet` will always give a different
`ObjectSet`*.

*: If there is no dynamic data, the result will always be the same. Also depending of how [Faker][1] is seeded
(`null` or a positive integer), it data will be generated more or less randomly. For a given seed,
the data generated will always be the same, for a seed set to `null`, the result will always be different.

The generation of the data is done in the following order:
 
1. Resolve `FixtureSet`
    1. Resolve the parameters: it is assumed the injected parameters are already resolved. Existing parameters are
    overriden by the local ones if they conflicts.
    2. Resolve the fixtures: a fixture may have *flags*, used for having
    [templates](https://github.com/nelmio/alice/blob/master/doc/fixtures-refactoring.md#fixture-inheritance) for example.
    This resolution step is where it is possible to alter the collection of fixtures depending of the flags. For the
    templates for example, this is the step where a `dummy` fixture inheriting from another one `base_dummy`, will
    effectively inherit from the properties of `base_dummy` and `base_dummy` will be removed of the list of fixtures
    (templates are not generated).
1. Instantiation: thanks to an `Instantiator`, this is the step where *all* fixtures will be instantiated. The
instantiation try to be smart enough to account for the order. For example if `second_dummy` is being instantiated but
requires `first_dummy` to be instantiated first, instead of failing because the order is wrong like in 2.x, the
instantiator will instantiate `first_dummy` before resuming the instantiation of `second_dummy`.
1. Hydration: thanks to an `Hydrator`, is the step where all PHP objects are hydrated, i.e. where all the property
values will be set.
1. "Caller": thanks to a `Caller`, some additional function calls can be made on the PHP objects after instantiation and
hydration.

During the instantiation, hydration or calling process, values may be passed. Those values are a reference to another
fixture, a parameter, a static value, but sometimes it can also be a value that must be generated via faker. For example
`<name()>` will result in a random name. Those values are always resolved on the fly by a `ValueResolver`.

To see more about the fixture lifecycle, please check [#388](https://github.com/nelmio/alice/issues/388).

To have more detail regarding a class, the easiest way is to check the code itself and the tests."

## Expression Language

As already mentionned, alice ship with an Expression Language, responsible to interpret values such as `@user*` or
`<curent()>`. The complete list of supported features can be found in [ParserIntegrationTest](tests/FixtureBuilder/ExpressionLanguage/Parser/ParserIntegrationTest.php)
with the [original RFC](https://github.com/nelmio/alice/issues/377).



## Contributing

The project is using the [PHPUnit][2] for tests. The library also include framework bridges like
[for Symfony](https://github.com/nelmio/alice/tree/master/src/Bridge/Symfony) which amounts to registering the right
services with the right properties like tags and configuration to the framework Dependency Injection Container. Any
other framework special features should be done in another library, bundle, module etc. 

To avoid any conflicts, the framework dependencies used by the bridges are installed in dedicated folders thanks to
[bamarni composer plugin][3]. As a result, if you want to run the tests for Symfony, you must run the tests with
`phpunit_symfony.xml.dist` instead of `phpunit.xml.dist`.

The test suite is also making use of the groups annotations:

- `integration`: integration tests
- `symfony`: Symfony bridge related tests
- no group: any unit test

For example to run only the Symfony bridge integration tests:

`$ vendor/bin/phpunit -c phpunit_symfony.xml.dist --group=integration,symfony`

The tests should be descriptive and are "testdox friendly" i.e. if you are using the testdox
option, you will get something like:

![Testdox](doc/img/testdox.png)

[1]: https://github.com/fzaninotto/Faker
[2]: https://github.com/sebastianbergmann/phpunit
[3]: https://github.com/bamarni/composer-bin-plugin
