# Advanced Guide

1. [Performance](#performance)
1. [Expression Language (DSL)](#expression-language-dsl)
    1. [Parameters](#parameters)
    1. [Functions](#functions)
    1. [Identity](#identity)
    1. [Arrays](#arrays)
    1. [Optional](#optional)
    1. [References](#references)
    1. [Property Reference](#property-reference)
1. [Extending Alice](#extending-alice)
    1. [Custom Flag](#custom-flag)
    1. [Custom Instantiation](#custom-instantiatior)
    1. [Custom Accessor](#custom-accessor)


## Performance

The main performance bottleneck is usually the unique values handling. As you
can see in the [UniqueValueResolver](../src/Generator/Resolver/Value/Chainable/UniqueValueResolver.php#L100-L107),
the resolver tries to generate a value, check if that value has already been
generated for a given scope (the check is rather expensive), and try to generate
another one again until it either get a new value or the maximum number of
attempt is reached.

If you are interested in seeing how huge the impact can be, you can compare
[the scenario 2 profiling fixtures](../profiling/scenario2/fixtures.yml) with
[the scenario 3 profiling fixtures](../profiling/scenario3/fixtures.yml) with
[Blackfire](https://blackfire.io).

A the moment, alice does not focus on generating huge sets of object either
(lack of use cases) as alice objects are often persister after with an ORM,
ORM which are not designed to persist huge sets of objects. If however you are
interested in that scenario, feel free to investigate ;)


## Expression Language (DSL)

### Parameters

`'<{X}>'` where `X` is evaluated and can be anything.

**Warning:** nested parameters are currently not supported.

Escaped expression: `'\<{X}>'` (will return the string `'<{X}>'`).

Example:

```yaml
parameters:
    foo: 'bar'

stdClass:
    dummy:
        foo: '<{foo}>'
        escapedFoo: '\<{foo}>'
```

will result in:


```
[
    'dummy' => stdClass {
       +"foo": "bar",
       +"escapedFoo": "<{foo}>",
    }
]
```


### Functions

`'<function(X)>'` where `X` is evaluated and can be anything.

`function` can be a Faker or a PHP native (or registered in the global scope) function.

Escaped expression: `'\<function(X)>'` (will return the string `'<function(X)>'`).

```yaml
stdClass:
    dummy:
        functionValue: '<strtolower("BAR")>'
        nestedFunctionValue: '<strtolower(<(implode(" ", ["HELLO", "WORLD", \<foo()>]))>)> \<bar()>'
```

will result in:


```
[
    'dummy' => stdClass {
        +"functionValue": "bar",
        +"nestedFunctionValue": "hellow world <bar()>",
    }
]
```


### Identity

`'<(X)>'` shortcut for `<identity(X)>`

Returns `X` evaluated as a PHP element, e.g. `2 + $nbr` (provided `$nbr === 2`) will result in`4`.
When using the identity function, you will still have access to:

- The value current (result of `<current()>`) with the variable `$current`
- The existing variables
- The reference to another fixture (via the `@fixtureId` notation)

Escaped expression: `'\<(X)>'` (will return the string `'<(X)>'`).

Example:

```yaml
# Simple example
stdClass:
    dummy:
        foo: '<(strtolower("BAR"))>'
```

will result in:


```
[
    'dummy' => stdClass {
        +"foo": "bar",
    }
]
```

```yaml
# Example with current
stdClass:
    dummy{1..2}:
        foo: '<($current)>'
```

will result in:


```
[
    'dummy1' => stdClass {
        +"foo": "1",
    },
    'dummy2' => stdClass {
        +"foo": "2",
    }
]
```

```yaml
# Example with variables and references
stdClass:
    dummy1:
        foo: '<($current)>'
```

will result in:


```
[
    'dummy1' => stdClass {
        +"foo": "1",
    },
    'dummy2' => stdClass {
        +"foo": "2",
    }
]
```


### Arrays

**Regular arrays**: `[X, Y, ... Z]`.
Each element of the array is evaluated and can be anything.

**String arrays**: `'Xx Y'`
- `X` must be either a positive integer or a function/param, e.g. `10` or `<numberBetween(0,20)>`
- `Y` must be a non-empty string
- This expression cannot be surrounded, i.e. `'foo 10x bar'` is invalid and `'10x foo bar'` match the above pattern with `X='10'` and `Y='foo bar'`.

String array `'[X, Y, ... Z]'`

Similar to the regular array, the difference is that the annotation is a string one, useful for being able to use the previous pattern with arrays: `'10x [@user*->name, @group->name]'`.

The expression cannot be surrounded either, otherwise will be interpreted as a plain string (this does not mean that an element could not be evaluated, but it certainly won't return the expected result.

Escaped expression: `'\[X]'` (will return the string `'[X]'`.


### Optional

`D%? X: Y` where `D` must be an element of `]0;100[` and `X`, `Y` are evaluated
- If `Y` is `null`, the condition is written `D%? X` instead.
- `X` must be an non-empty string and must not contain the `:`
- `Y` must be a non-empty string and must not contain a space as it is used as a delimiter to determine the end of `Y`. So `20%? foo: bar baz` will be matched with `(D, X, Y)=('20', 'foo', 'bar')` (`'baz'` is not part of `Y`).


### References

`@ref` where `ref` is evaluated as a reference.

`ref` must be first resolved like any value to get a string from it. Once done, the resulting value can be used to find a reference matching that mask.

Examples:
- `@user{1..2}` => `@user1` or `@user2`
- `@user*`
- `@user_{alice, bob}` => `@user_alice` or `@user_bob`
- `@user<numberBetween(1,20)>` => `@userX`,
- `@user<current()>`

There is obvious limits to this annotation: we cannot use the optional pattern with it, e.g. `'@user50%? foo'` but they are such edge cases and really complicated, I am not willing to support them. In other words, `ref` is limited to:
- characters allowed by references
- `*` character (for wildcards)
- `{}`, digits and `.` (for lists)
- `'<X>'` pattern

Escaped expression: `'\@ref'` will return `'@ref'`


### Property reference

`@ref->prop` where `@ref` is evaluated (like described in the References section) (but not `prop`).

`prop` refers to a property of the object pointed by `@ref` and is accessed via a property accessor (which will determined which getter to use if a getter is required or get it directly if it is a public property). For the sake of simplicity, it is assumed that properties are plain english and as such should only be composed of letters or underscores `_`.

As `prop` is not resolved, having `@user1->name<current()>` will be equivalent to `Alice<current()>` if `@user1->name` results in `'Alice'`.



## Extending Alice

### Custom Flag

Flags are being parsed by the [FlagParser](../src/FixtureBuilder/Denormalizer/FlagParserInterface.php).
You can easily decorate alice built-in flag parser or add a
[chainable flag parser](../src/FixtureBuilder/Denormalizer/FlagParser/Chainable).

How the flag is then interpreted and influence the generation of the object
highly depends on the purpose of the flag. For example the `ExtendFlag` and
`TemplateFlag` are handled by `TemplateFixtureBagResolver` which resolves
the dependencies to generate a clean collection of fixture (replaces the
templates/extends by their real values).


### Custom Instantiator

The instantiation is being handled by the [InstantiatorInterface](../src/Generator/InstantiatorInterface.php).
By default, you have a set of instantiator to handle several use cases: regular constructor, factory
or without any instantiator (via Reflection).

If you need to customize the way a fixture is instantiated, for example like done
in [HautelookAliceBundle](https://github.com/hautelook/AliceBundle/blob/master/src/Alice/Generator/Instantiator/Chainable/InstantiatedReferenceInstantiator.php),
to be able to use a service registered to Symfony Dependency Injection Container
to instantiate a given fixture, you can easily add a chained instantiator.

Another way to extend the instantiation is to simply decorate Alice built-in
instantiator, for example:


```php
<?php

namespace App;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;

final class DummyInstantiator implements InstantiatorInterface
{
    private $instantiator;

    public function __construct(InstantiatorInterface $decoratedInstantiator)
    {
        $this->instantiator = $decoratedInstantiator;
    }

    /**
     * @inheritdoc
     */
    public function instantiate(
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): ResolvedFixtureSet
    {
        if ('App\Dummy' === $fixture->getClassName()) {
            $instance = new Dummy();

            $objects = $fixtureSet->getObjects()->with(
                new SimpleObject(
                    $fixture->getId(),
                    $instance
                )
            );

            return $fixtureSet->withObjects($objects);
        }

        return $this->instantiator->instantiate($fixture, $fixtureSet, $context);
    }
}
```

### Custom Accessor

Guessing accessors is always a tricky job. Alice does it by relying on the
[Symfony Property Component][1]
which assumes you are using PSR-2. This does not however cover all the use
cases which may lead you to extend that behaviour. To achieve that, you
have 2 easy ways.


1. Create a custom `PropertyAccessor`.

By far the simplest solution, this is actually what is done for setting the
values of an `stdClass` object (see [StdPropertyAccessor](../src/PropertyAccess/StdPropertyAccessor.php))
or for setting private properties using reflection (see [`ReflectionPropertyAccessor`](../src/PropertyAccess/ReflectionPropertyAccessor.php))

You can decorate the property accessor to use your own [by extending the `NativeLoader`](../fixtures/Loader/WithReflectionLoader.php)
or by using the decoration feature of the Symfony DI component when using the provided integration:

```yml
services:
    app.fixtures.reflection_property_accessor:
        class: Nelmio\Alice\PropertyAccess\ReflectionPropertyAccessor
        public: false
        decorates: nelmio_alice.property_accessor
        decoration_priority: -10
        arguments: ['@app.fixtures.reflection_property_accessor.inner']
```


2. Decorate the PropertyHydrator

Setting a value is done by a [PropertyHydratorInterface](../src/Generator/Hydrator/PropertyHydratorInterface.php).
By default, Alice uses [SymfonyPropertyAccessorHydrator](../src/Generator/Hydrator/Property/SymfonyPropertyAccessorHydrator.php).
which is a simple adapter for [Symfony Property Component][1]. So you could easily
decorate it to do what you want:

```php
<?php

namespace App;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\ObjectInterface;

final class DummyPropertyHydrator implements PropertyHydratorInterface
{
    private $hydrator;

    public function __construct(PropertyHydratorInterface $decoratedPropertyHydrator)
    {
        $this->hydrator = $decoratedPropertyHydrator;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(ObjectInterface $object, Property $property, GenerationContext $context): ObjectInterface
    {
        if ('key' === $property->getName()) {
            $instance = $object->getInstance()->withKey($property->getValue());

            return new SimpleObject($object->getId(), $instance);
        }

        return $this->hydrator->hydrate($object, $property, $context);
    }
}
```


<br />
<hr />

« [Customize Data Generation](customizing-data-generation.md) • [Contribute](../CONTRIBUTING.md) »


[1]: http://symfony.com/doc/current/components/property_access.html
