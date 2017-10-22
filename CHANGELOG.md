### 3.1.0 (2017-10-22)

### Features

* Add new multifile loader (#821)

### Bugfixes

* Make loaders aliases public (#813)
* Fix PHPUnit Bridge deprecation (#818)
* Update unclonable trait (#820)

### Other changes highlight

* Add Project name in trigger error deprecation notices (#810)
* Makefile tweaks & Update deps (#804)
* Fix PHPUnit Bridge deprecation (#818)
* Mention about `addXxx()` methods in upgrade path (#791)


### 3.0.1 (2017-09-14)

### Bugfixes

* Combine string token followed by reference (#783)

### Other changes highlight

* Fix the MakeFile (#790)


### 3.0.0 (2017-09-12)

### Features

* Add support for Doctrine Inflector (#548)
* Add support of embedded couchdb document (couchdb-odm) (#683)
* Add range named builder (#693)

### Deprecations

* Deprecate the usage of the local flag (#557)
* Deprecate the usage of date strings (#559)
* Deprecate optional values with floats (#564)
* Deprecate references in quotes (#566)
* Deprecate setting private or protected props directly (#607)
* Deprecate usage of non PSR-1 compliant setters (#608)
* Deprecate usage of inaccessible constructors (#609)
* Deprecate calling methods (#610)
* Deprecate array hash uniqueness (#611)

### Bugfixes

* Allow a fixture specs to be null (#767)


#### Bugfixes

* [DX] Properly handle missing specs (#774)
* Fixed array function arguments (#775)
* Fixed string arguments for function calls (#787)


### 3.0.0-rc.1 (2017-06-29)

#### Bugfixes
* Attempts to resolved objects completely whenever possible (#756)


### 3.0.0-rc.0 (2017-05-21)

#### Features
* Add a ReflectionPropertyAccessor to access to private and protected properties (#716)
* Make Faker seed configurable (#726)
* Add the factory keyword (#729)
* Add support for immutable functions (#731)

#### Bugfixes
* Fix error on static factory with associative array (#714)
* Fix a TypeError thrown when objected needed complete generation (#715)
* Fix array value behaviour (#728)

#### Other changes highlight
* Move `deep_clone` in Alice namespace (#727)
* Rework callers (#734)
* Add Makefile script (#737)


### 3.0.0-beta.5 (2017-04-11)

#### Features
* Prepare for PHPUnit 6 (#705)
* Provide compatibility with PHP 7.2 (#706)


#### Bugfixes
* Create FixtureNotFoundException for resolver context (#713)


### 3.0.0-beta.4 (2017-03-19)

#### Features
- Implements method callers (#666)
- Add method call reference resolver (#669)
- Make NativeLoader non-final and changed its API (#681)
- Make parameters accessible in identity (#698)

### Bugfixes
- Fix edge case on OptionalValueResolver (#671)
- Fix escaping of the percent sign (#687)
- Fix usage of underscore in the function name (#702)


### 3.0.0-beta.3 (2016-12-16)

#### Features
- Add a context cache (#656)

#### Bugfixes
- Fix wildcard resolver cache (#657)
- Fix usage of dynamic array with functions (#655)


### 3.0.0-beta.2 (2016-12-02)

#### Features
- Add a Faker provider to cast values (#622)
- Allow to whitelist some PHP native functions (#640)

#### Bugfixes
- Fix optional flag boundaries (allow extremums `0` and `100`) (#623)
- Lowered dynamic array quantifier minimal value (allow `0` and `1`) (#624)
- Fix unique scope for arrays (#634)
- Fix function which contains numerical characters tokenization (#649)

#### Other changes highlight
- Rework blackfire scripts (#633) 
- Replace the exception thrown for unclonable tests (#637)
- Symfony Bundle configuration tweaks (#641)


### 3.0.0-beta.1

### Features
- Better handling of references to inexistent fixtures (#592)
- Automatically escape the `@` character in words (#593)

#### Bugfix
- Move PhpUnitBridge dependency to dev dependencies (#576)
- Fix support for stdClass classes (#595)
- Fix complete generation process (#596)
- Fix FlagBag immutability (#614)
- Fix UniqueValuesPool cached value check (#615)
- Make ObjectBag more performant (#617)
- Fix performance issues with Wildcard resolver (#618)
- Fix usage of `$current` with identity (#619)

#### Changes highlight
- Harden tests (higher coverage and make them more robust thanks to Humbug) (#571, #590)
- Simplify mutli-dep management with (#582)
- Make testing easier (#583, #603)
- Enforce strict types everywhere (#587)
- Tag the Expression Language as internal (#600)
- Add scenarios for profiling (#613, #616)


### 3.0.0-beta.0 (2016-09-14)

Main differences between 2.x and 3.x:

The persistence layer has been removed
Rewrite of the API to make it more extensible and more robust
Change in the architecture to address some limitations found in 2.x
More details on the architectural changes can be found
[here](https://github.com/nelmio/alice/blob/master/CONTRIBUTING.md#architecture).


### 2.3.2 (2017-10-20)

### Misc

* Add tests to pin the behaviour in case of duplicate fixtures (#766)
* Add vendor name in trigger error deprecation notices (#807)
* Remove useless extra option (#817)
* Make invalid scenarios as passing tests instead of skipped ones (#814)
* Added tests to ensure setting a private/protected property is deprecated (#816)


### 2.3.1 (2017-04-14)

### Bugfixes

* Prepare for PHPUnit 6 (#707)
* Prepare for PHP 7.2 (#708)
* Fixed required dependencies (#707)


### 2.3.0 (2017-03-18)

#### Features

* Add support for Doctrine Inflector (#548)
* Add support of embedded couchdb document (couchdb-odm) (#683)
* Add range named builder (#693)

#### Deprecations

* Deprecate the usage of the local flag (#557)
* Deprecate the usage of date strings (#559)
* Deprecate optional values with floats (#564)
* Deprecate references in quotes (#566)
* Deprecate setting private or protected props directly (#607)
* Deprecate usage of non PSR-1 compliant setters (#608)
* Deprecate usage of inaccessible constructors (#609)
* Deprecate calling methods (#610)
* Deprecate array hash uniqueness (#611)

#### Bugfixes

* Remove usage of the deprecated method getMock (#558)
* Document the change of behaviour of the identity function (#560)
* Avoid using the GLOB_BRACE flag when it is not supported (#573)


### 2.2.2 (2016-07-15)

#### Bugfixes

* Fix support for hyphens (#431)


### 2.2.1 (2016-07-14)

#### Bugfixes

* Fix a BC break on how parameters were captured (#423)
* Fix the usage of empty values with Faker provider (#426)

#### Miscellaneous

* Add tests for the usage of typehint in setters (#427)
* Add tests for the usage of templates declared in an included file (#424)


### 2.2.0 (2016-07-11)

#### Features

* Add support for snake_case properties (#323)
* Add support for dots in reference names (#312)
* Add support for Fixture parameters in PHP File (#341)
* Don't persist ORM entities (embeddable support) (#272)
* Enable quoting references to reflect on the changes in Symfony YAML Parser (#305)

#### Deprecations

* Drop support for PHP 5.4 and 5.5 (#414)
* Deprecate usage of the the range operator with more than two dots (#329)
* Deprecate usage of custom context in Parsers (#342)

#### Bugfixes

* Fix singularify deprecation warnings and optimize method detection (#407)
* Fix various bugs in #355:
  - `user_{alice, bob,}` previously was building a reference named `user_{alice, bob,}`. Now builds that as a list, i.e. result in `user_alice` and `user_bob`. A deprecation warning is also thrown to warn the user that the list is poorly formatted and an exception will be thrown in v3.
  - `user_{, alice, bob}`: same as previous case.
  - `user_{0..2}`: value for `<current()>` were respectively `'0'`, `1`, `2`; Now are all strings as states the phpdoc. Changed in #339.
  - `user_{0....2}`: was generating only one fixture named `user_{0....2}`; Now is equivalent to `user_{0...2}`
  - `user_{2...0}`: as reported in #358 was generating 4 fixtures... Now is equivalent to `0...2` which result in `user_0`, `user_1` and `user_2`
  - `user_{2...2}`: was generating two fixtures `user_1` and `user_3`; Now doesn't build any (the segment is `[2;2[` so contains no element)
  - `user_{0.2}`: was generating a fixture named `user_{0.2}`; Now doesn't generate any.
  - `user_{2..}`: was generating a fixture named `user_{2..}`; Now doesn't generate any.
  - `user_{-1..2}`: was generating a fixture named `user_-1..2`; Now doesn't generate any. Same goes for all ranges containing a negative number
  - Deprecate the silent failing occurring when a fixture could not be built by the builder: current returns `null`, will throw an exception in the future.
* Fix unique flag usage with templates (#359)
* Fix some phpdoc (#264)
* Fix the order in which the files were included (#314)


### 2.1.4 (2016-01-07)

#### Bugfixes

* Ensure named static constructors are preferred over reflection (#303)


### 2.1.3 (2015-12-28)

#### Features

* Add support for static constructors (#301)


### 2.1.2 (2015-12-10)

#### Features

  * Add support for Symfony3 (#290, #287, #296)

#### Bugfixes

  * Allow the Populator to set private properties of a parent class (#282)


### 2.1.1 (2015-10-01)

  * Bug fixes / cleanup in `Fixtures` static class
  * Bug fix in `Reference` processor to enable parsing zero references
  * Bug fix to handle default instance with no properties
  * Performance improvements


### 2.1.0 (2015-09-06)

  * Added support for array parameters
  * Fixed bug handling addXXX setters
  * Fixed bug handling functions without signature that use func_get_args() to retrieve parameters


### 2.0.0 (2015-03-17)

  * Expanded public interface of the `Loader` class to support:
    - Trivial parsing of new document types
    - Custom instantiation methods
    - Custom setting of properties
    - Custom processing and fixture building
  * Added support for using parameters with the following
    ```
    parameters:
        foo: value

    Acme\ClassName:
        property: <{foo}>
    ```


### 1.7.2 (2014-10-10)

  * Fixed support for custom providers, using addProvider instead of setProviders lets you add single providers


### 1.7.1 (2014-09-29)

  * Fixed handling of non-existing files
  * Fixed support for fixture inheritance combined with fixture ranges


### 1.7.0 (2014-04-24)

  * Added fixture inheritance with `(template)` and `(extends NAME)` flags
  * Added support for including other yaml fixtures files using a top-level `include` array
  * Added an `<identity()>` (aliased as `<()>` faker provider to just evaluate PHP expressions with variables
  * Added `@self` reference which is the equivalent to `$this`
  * Added support for passing references to faker providers
  * Added support for HHVM and PHP 5.6


### 1.6.0 (2014-02-05)

  * The array of instances returned by load() now has the fixture name as key
  * Added support for static factory methods to replace __construct
  * Added a special __set property to define a custom setter for all properties
  * Added support for @-references within faker provider calls e.g. `<foo(@obj)>`
  * Added support for escaping @-signs to create literal strings starting with `@`, e.g. `\@foo`
  * Fixed support for null variable references
  * Fixed invalid invocation of private setters


### 1.5.2 (2013-08-07)

  * Fixed typo in the handling of processors
  * Fixed regression in handling of <current()> in constructors


### 1.5.1 (2013-08-01)

  * Fixed persist_once issue, it is now disabled by default again
  * Fixed regression in handling of suppressed constructors


### 1.5.0 (2013-07-29)

  * Added extensibility features to allow the creation of a Symfony2 AliceBundle (hautelook/alice-bundle)
  * Added possibility to fetch objects by id with non-numeric ids
  * Added `(local)` flag for classes and objects to create value objects that should not be persisted
  * Added enums to create multiple objects (like fixture ranges but with names)
  * Added ProcessorInterface to be able to modify objects before they get persisted
  * Fixed cross-file references, everything is now persisted at once
  * Fixed self-referencing of objects
  * glob patterns now accept braces (http://php.net/manual/en/function.glob.php)


### 1.4.0 (2013-04-15)

  * Added possibility to mark fields as unique, so that random values are generated uniquely
  * Added a logger option which can be a callable or PSR-3 logger and will receive basic progress information
  * Added support for symfony 2.3
  * Fixed caching of loader objects in the factory method to handle different options given for each loader


### 1.3.0 (2013-01-22)

  * Added support for multi and random references together with properties: `5x @user*->property`


### 1.2.0 (2013-01-06)

  * Added support for calling methods: `methodName: [arg, arg2, ..]`
  * Added support for passing constructor arguments: `__construct: [arg, arg2]`
  * Added possibility to bypass constructors by setting: `__construct: false`


### 1.1.0 (2012-12-05)

  * Added possibility to reference an object's properties via `@reference->property`


### 1.0.0 (2012-11-22)

  * Initial release
