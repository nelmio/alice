### 2.0.0 (2014-XX-XX)

  * Removed ability to pass `callable` for logging in favor of PSR-3 `LoggerInterface` only
  * Added PersistEvent dispatched as `PRE_PROCESS` and `POST_PROCESS` event
  * Added PersisterInterface replacing container, decoupling from Doctrine
  * Added `PersistenceAwareLoaderInterface` reflecting a `LoaderInterface` capable of referencing related objects

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
