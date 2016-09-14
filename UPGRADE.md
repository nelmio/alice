# Breaking changes between Alice 2.x and 3.0

Alice 3.0 comes as a complete rewrite which shares nothing in common with 2.x
API wise. The reasons for those changes are:

- Removing the persistence layer. This work should be handled in Alice extensions
  such as [AliceDataFixtures](https://github.com/theofidry/AliceDataFixtures)
- Introduce a proper fixture lifecycle (building, instantiation,
  hydration and configuration step)
- Introduce a proper Lexer

The aim of those changes are to make Alice more robust, performant and
extensible. The technical debt and a lot of weird edge cases were extremely hard
to in 2.x due to its design.

If you are maintaining an extension, bundle or module for Alice, chances are
high that you have to redo a big chunk of work. However if you are just a
simple consumer, little has changed:

- The loading is now done via the new loader `Nelmio\Alice\Loader\NativeLoader`
- You can easily injected parameters and objects to this loader
- The loader now returns a set containing both the parameters resolved and the
  objects created (in 2.x, only the objects were returned)
- Frameworks bridges are integrated to Alice, so if you are using Symfony for
  example the laoder is accessible via the `nelmio_alice.data_loader` or
  `nelmio_alice.file_loader`

The amount of BC breaks user-land (i.e. in the file declaration syntax) have
been reduced to the bare minimum. The one introduced are either edge cases
where the result could not be guaranteed or syntax errors. Whenever possible,
a deprecation message about those changes will land in the patch versions of
Alice 2.x.

Alice 2.x is no longer supported. Some bug fixes or improvements may still be
done depending of the case, but no Alice maintainer will actively work on it
(PR may still be welcomed though).


# Breaking changes between Alice 1.x and 2.0

Support for PHP 5.3 has been removed (most notably, we're using short array
syntax), and we are no longer testing in CI against 5.3.

In php fixtures, and nested faker blocks in yml fixtures, using `$this->fake()`
or `$loader->fake()` is no longer supported.

A closure is provided so you can now use the shorter `$fake()` instead.


## New public interface

### Fixtures

The `Fixtures` interface has not been altered.


### Loader

The public interface of the `Loader` class has been expanded to include the
following methods:

* `addParser`: Parsers handle new types of files
* `addProcessor`: Processors handle new ways to generate properties
* `addBuilder`: Builders handle the generation of fixtures themselves
* `addInstantiator`: Instantiators handle creating instances
* `addPopulator`: Populators handle setting properties on instances

All of the feature set of Alice 1.x that was previously in the `Loader` class
has been pieced out into the handlers mentioned above - a quick glance at the
class names will probably explain their purpose as well as any documentation
could.