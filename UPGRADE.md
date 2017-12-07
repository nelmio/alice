# Breaking changes between Alice 2.x and 3.0

Alice 3.0 comes as a complete rewrite which shares nothing in common with 2.x
API wise, but only a few changes user-land. The reasons for those changes are:

- Removing the persistence layer. This work should be handled in Alice extensions
  such as [AliceDataFixtures](https://github.com/theofidry/AliceDataFixtures)
- Introduce a proper fixture lifecycle (building, instantiation,
  hydration and configuration step)
- Introduce a proper Lexer
- Make it easier to extend the library

The aim of those changes are to make Alice more robust, performant and
extensible. The technical debt and a lot of weird edge cases were extremely hard
to fix in 2.x due to its design.

If you are maintaining an extension, bundle or module for Alice, chances are
high that you have to redo a big chunk of work. However if you are just a
simple consumer, very little changed:

- The loading is now done via the new loader `Nelmio\Alice\Loader\NativeLoader`
- You can easily inject parameters and objects into this loader
- The loader now returns a set containing both the parameters resolved and the
  objects created (in 2.x, only the objects were returned)
- Framework bridges are integrated into Alice. If you are using Symfony for
  example the loader is accessible via the `nelmio_alice.data_loader` or
  `nelmio_alice.file_loader` service

The amount of BC breaks user-land (i.e. in the file declaration syntax) have
been reduced to the bare minimum. Those introduced are either edge cases
where the result could not be guaranteed, or syntax errors. Whenever possible,
a deprecation message about those changes will land in the patch versions of
Alice 2.x.

Alice 2.x is no longer supported. Some bug fixes or improvements may still be
done depending on the case, but no Alice maintainer will actively work on it
(PR may still be welcomed though).

## User-land changes

- `addXxx()` methods are no longer supported, you need to define a setter for the collection.

    ```php
    class Recipe
    {
        // no longer supported
        public function addServing(Serving $serving)
        {
            // …
        }
        
        // the setter must be defined
        public function setServings(iterable $servings)
        {
            // …
        }
    }
    ```
    
    This change is mostly the result of moving from a custom property accessor to the
    [Symfony Property Access Component](https://symfony.com/doc/current/components/property_access.html)
    which does not support this.
    
    [See the original discussion](https://github.com/nelmio/alice/issues/654)
