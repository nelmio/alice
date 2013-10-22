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
  * glob patterns now accept braces

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
