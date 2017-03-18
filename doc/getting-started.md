# Getting Started

## Basic Usage

The easiest way to use this is to call the static `Nelmio\Alice\Fixtures::load`
method. It will bootstrap everything for you and return you a set of persisted
objects in the container you give it.

Examples:

```php
// Load a yaml file into a Doctrine\Common\Persistence\ObjectManager object
$objects = \Nelmio\Alice\Fixtures::load(__DIR__.'/fixtures.yml', $objectManager);

// Load a php file into a Doctrine\Common\Persistence\ObjectManager object
$objects = \Nelmio\Alice\Fixtures::load(__DIR__.'/fixtures.php', $objectManager);
```

Note: You can also pass an array of filenames if you have multiple files with
references spanning more than one.


### Options

`Fixtures::load` accepts a third `$options` argument that is an array
with the following keys:

- locale: the default locale
- providers: an array of additional Faker providers
- seed: a seed to make sure Faker generates data consistently across runs, set
  to null to disable (defaults to 1)
- logger: a callable or `Psr\Log\LoggerInterface` object that will receive progress
  information during the loading of the fixtures
- persist_once: only persist objects once if multiple files are passed, by default
  objects are persisted after each file


## Detailed Usage

If you want a bit more control you can instantiate the various object yourself
and make it work just as easily:

```php
// Load objects from a yaml file
$loader = new \Nelmio\Alice\Fixtures\Loader();
$objects = $loader->load(__DIR__.'/fixtures.yml');

// Optionally persist them into the doctrine object manager
// you can also do that yourself or persist them in another way
// if you do not use doctrine
$persister = new \Nelmio\Alice\Persister\Doctrine($objectManager);
$persister->persist($objects);
```

This loader maintains its list of built objects, so `load` can be called multiple times with different files if your fixture file starts growing unmanageably large.

Using the `Loader` class directly also allows you to add more customization to how your objects are instantiated, properties are set, and what kinds of files you can parse. The following methods are all available for these purposes:

* `addParser`: Parsers handle new types of files
* `addProcessor`: Processors handle new ways to generate properties
* `addBuilder`: Builders handle the generation of fixtures themselves
* `addInstantiator`: Instantiators handle creating instances
* `addPopulator`: Populators handle setting properties on instances

> **Note**: To load plain PHP files, the files must return an array containing the same structure as the yaml files have.


<br />
<hr />

« [Installation](../README.md#installation) • [Complete Reference](complete-reference.md) »
