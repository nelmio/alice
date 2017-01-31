# Breaking changes between Alice 2.x and 3.0

Alice 3.0 comes as a complete rewrite which shares nothing in common with 2.x
API wise, but only a few changes user-land. The reasons for those changes are:

- Removing the persistence layer. This work should be handled in Alice extensions
  such as [AliceDataFixtures](https://github.com/theofidry/AliceDataFixtures)
- Introduce a proper fixture lifecycle (building, instantiation,
  hydration and configuration step)
- Introduce a proper Lexer
- Make it easier to extend the library

The aim of those changes are to make Alice more robust, efficient and
extensible. The technical debt and a lot of weird edge cases were extremely hard
to fix in 2.x due to its design.

If you are maintaining an extension, bundle or module for Alice, chances are
high that you have to redo a big chunk of work. However if you are just a
simple consumer, very little hanged:

- The loading is now done via the new loader `Nelmio\Alice\Loader\NativeLoader`
- You can easily injected parameters and objects to this loader
- The loader now returns a set containing both the parameters resolved and the
  objects created (in 2.x, only the objects were returned)
- Frameworks bridges are integrated to Alice. If you are using Symfony for
  example the loader is accessible via the `nelmio_alice.data_loader` or
  `nelmio_alice.file_loader` service

The amount of BC breaks user-land (i.e. in the file declaration syntax) have
been reduced to the bare minimum. The one introduced are either edge cases
where the result could not be guaranteed or syntax errors. Whenever possible,
a deprecation message about those changes will land in the patch versions of
Alice 2.x.

Alice 2.x is no longer supported. Some bug fixes or improvements may still be
done depending of the case, but no Alice maintainer will actively work on it
(PR may still be welcomed though).

Other notable user-land BC breaks not covered by deprecation notices:

- Change in the behaviour of the identity function (https://github.com/nelmio/alice/pull/560)
- Function arguments are no longer parsed as PHP arguments (https://github.com/nelmio/alice/issues/498)

More information can be found regarding architectural changes in
[Alice 3.x contributing notes](https://github.com/nelmio/alice/blob/master/CONTRIBUTING.md).


# Breaking changes between Alice 1.x and 2.0

Support for PHP 5.3 has been removed (most notably, we're using short array syntax), and we are no longer
testing in CI against 5.3.

In PHP fixtures, and nested faker blocks in yml fixtures, using `$this->fake()`
or `$loader->fake()` is no longer supported.

A closure is provided so you can now use the shorter `$fake()` instead.


## New public interface


### Fixtures

The `Fixtures` interface has not been altered.


### Loader

The public interface of the `Loader` class has been expanded to include the following methods:

* `addParser`: Parsers handle new types of files
* `addProcessor`: Processors handle new ways to generate properties
* `addBuilder`: Builders handle the generation of fixtures themselves
* `addInstantiator`: Instantiators handle creating instances
* `addPopulator`: Populators handle setting properties on instances

All of the feature set of Alice 1.x that was previously in the `Loader` class has been pieced out into the handlers mentioned above - a quick glance at the class names will probably explain their purpose as well as any documentation could.
