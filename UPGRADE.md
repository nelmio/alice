# Breaking changes between Alice 1.x and 2.0

Support for PHP 5.3 has been removed (most notably, we're using short array syntax), and we are no longer testing in CI against 5.3.

In php fixtures, and nested faker blocks in yml fixtures, using `$this->fake()`
or `$loader->fake()` is no longer supported.

A closure is provided so you can now use the shorter `$fake()` instead.

# New public interface

## Fixtures

The `Fixtures` interface has not been altered.

## Loader

The public interface of the `Loader` class has been expanded to include the following methods:

* `addParser`: Parsers handle new types of files
* `addProcessor`: Processors handle new ways to generate properties
* `addBuilder`: Builders handle the generation of fixtures themselves
* `addInstantiator`: Instantiators handle creating instances
* `addPopulator`: Populators handle setting properties on instances

All of the feature set of Alice 1.x that was previously in the `Loader` class has been pieced out into the handlers mentioned above - a quick glance at the class names will probably explain their purpose as well as any documentation could.