<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures;

use Nelmio\Alice\Instances\Processor\Methods\Faker;
use Nelmio\Alice\Fixtures\ParameterBag;
use Psr\Log\LoggerInterface;
use Nelmio\Alice\PersisterInterface;
use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\Instances\Instantiator;
use Nelmio\Alice\Instances\Populator;
use Nelmio\Alice\Instances\Processor;
use Nelmio\Alice\Instances\Processor\Providers\IdentityProvider;
use Nelmio\Alice\Util\TypeHintChecker;

/**
 * Loads fixtures from an array or file
 */
class Loader
{
    /**
     * @var Collection
     */
    protected $objects;

    /**
     * @var TypeHintChecker
     */
    protected $typeHintChecker;

    /**
     * @var Parser
     **/
    protected $parser;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var Faker
     */
    protected $fakerProcessorMethod;

    /**
     * @var Instantiator
     */
    protected $instantiator;

    /**
     * @var Populator
     */
    protected $populator;

    /**
     * @var PersisterInterface
     */
    protected $manager;

    /**
     * @var \Nelmio\Alice\Fixtures\ParameterBag
     */
    protected $parameterBag;

    /**
     * @var callable|LoggerInterface
     */
    private $logger;

    /**
     * @param string $locale     default locale to use with faker if none is
     *                           specified in the expression
     * @param array  $providers  custom faker providers in addition to the default
     *                           ones from faker
     * @param int    $seed       a seed  to make sure faker generates data consistently across
     *                           runs, set to null to disable
     * @param array  $parameters create loader with default parameters
     */
    public function __construct($locale = 'en_US', array $providers = [], $seed = 1, array $parameters = [])
    {
        $this->objects         = new Collection;
        $this->typeHintChecker = new TypeHintChecker;
        $this->parameterBag    = new ParameterBag($parameters);

        $allProviders = array_merge($this->getBuiltInProviders(), $providers);

        $this->processor = new Processor\Processor(
            $this->objects,
            $this->getBuiltInProcessors($allProviders, $locale)
        );

        $this->parser = new Parser\Parser(
            $this->getBuiltInParsers()
        );

        $this->builder = new Builder\Builder(
            $this->getBuiltInBuilders()
        );

        $this->instantiator = new Instantiator\Instantiator(
            $this->getBuiltInInstantiators($this->processor, $this->typeHintChecker)
        );

        $this->populator = new Populator\Populator(
            $this->objects,
            $this->processor,
            $this->getBuiltInPopulators($this->typeHintChecker)
        );

        if (is_numeric($seed)) {
            mt_srand($seed);
        }
    }

    /**
     * Loads a fixture file
     *
     * @param string|array $dataOrFilename data array or filename
     */
    public function load($dataOrFilename)
    {
        // ensure our data is loaded
        $data = !is_array($dataOrFilename) ? $this->parseFile($dataOrFilename) : $dataOrFilename;

        // create fixtures
        $newFixtures = $this->buildFixtures($data);

        // instantiate fixtures
        $this->instantiateFixtures($newFixtures);

        // populate objects
        return $this->populateObjects($newFixtures);
    }

    /**
     * Returns a reference to a fixture by name
     *
     * @param  string $name
     * @param  string $property optionally return only a given property of the reference
     * @return object
     */
    public function getReference($name, $property = null)
    {
        return $this->objects->find($name, $property);
    }

    /**
     * Returns all references created by the loader
     *
     * @return array[object]
     */
    public function getReferences()
    {
        return $this->objects->toArray();
    }

    /**
     * @param array $providers
     */
    public function setProviders(array $providers)
    {
        $this->fakerProcessorMethod->setProviders(array_merge($this->getBuiltInProviders(), $providers));
    }

    /**
     * @param object|array $provider Provider or array of providers
     */
    public function addProvider($provider)
    {
        $this->fakerProcessorMethod->addProvider($provider);
    }

    /**
     * @param array $references
     */
    public function setReferences(array $objects)
    {
        $this->objects->clear();
        foreach ($objects as $name => $object) {
            $this->objects->set($name, $object);
        }
    }

    /**
     * adds a processor for processing extensions
     *
     * @param Processor\Methods\MethodInterface $processor
     **/
    public function addProcessor(Processor\Methods\MethodInterface $processor)
    {
        $this->processor->addProcessor($processor);
    }

    /**
     * adds a parser for fixture parsing extensions
     *
     * @param Parser\Methods\MethodInterface $parser
     **/
    public function addParser(Parser\Methods\MethodInterface $parser)
    {
        $this->parser->addParser($parser);
    }

    /**
     * adds a builder for fixture building extensions
     *
     * @param Builder\Methods\MethodInterface $builder
     **/
    public function addBuilder(Builder\Methods\MethodInterface $builder)
    {
        $this->builder->addBuilder($builder);
    }

    /**
     * adds an instantiator for instantiation extensions
     *
     * @param Instantiator\Methods\MethodInterface $instantiator
     **/
    public function addInstantiator(Instantiator\Methods\MethodInterface $instantiator)
    {
        $this->instantiator->addInstantiator($instantiator);
    }

    /**
     * adds a populator for population extensions
     *
     * @param Populator\Methods\MethodInterface $populator
     **/
    public function addPopulator(Populator\Methods\MethodInterface $populator)
    {
        $this->populator->addPopulator($populator);
    }

    /**
     * parses a file at the given filename
     *
     * @param string filename
     * @return array data
     */
    protected function parseFile($filename)
    {
        return $this->parser->parse($filename);
    }

    /**
     * builds a collection of fixtures
     *
     * @param  array $rawData
     * @return array
     */
    protected function buildFixtures(array $rawData)
    {
        $fixtures = [];

        foreach ($rawData as $class => $specs) {
            $this->log('Loading '.$class);
            foreach ($specs as $name => $spec) {
                $fixtures[] = $this->builder->build($class, $name, null !== $spec ? $spec : []);
            }
        }

        return $fixtures ? call_user_func_array('array_merge', $fixtures) : [];
    }

    /**
     * creates an empty instance for each fixture, and adds it to our object collection
     *
     * @param array $fixtures
     */
    protected function instantiateFixtures(array $fixtures)
    {
        foreach ($fixtures as $fixture) {
            $this->objects->set(
                $fixture->getName(),
                $this->instantiator->instantiate($fixture)
            );
        }
    }

    /**
     * hydrates each instance described by fixtures and returns the final non-local list
     *
     * @param  array $fixtures
     * @return array
     */
    protected function populateObjects(array $fixtures)
    {
        $objects = [];

        foreach ($fixtures as $fixture) {
            $this->objects->set('self', $this->objects->get($fixture->getName()));
            $this->populator->populate($fixture);
            $this->objects->remove('self');

            // add the object in the object store unless it's local
            if (!$fixture->isLocal()) {
                $objects[$fixture->getName()] = $this->getReference($fixture->getName());
            }
        }

        return $objects;
    }

    /**
     * public interface to set the Persister interface
     *
     * @param PersisterInterface $manager
     */
    public function setPersister(PersisterInterface $manager)
    {
        $this->manager = $manager;
        $this->typeHintChecker->setPersister($manager);
    }

    /**
     * Set the logger callable to execute with the log() method.
     *
     * @param callable|LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return ParameterBag
     */
    public function getParameterBag()
    {
        return $this->parameterBag;
    }

    /**
    * Logs a message using the logger.
    *
    * @param string $message
    */
    public function log($message)
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->debug($message);
        } elseif ($logger = $this->logger) {
            $logger($message);
        }
    }

    /**
     * @return Processor\Methods\Faker
     */
    public function getFakerProcessorMethod()
    {
        return $this->fakerProcessorMethod;
    }

    /**
     * returns a list of all the default providers faker processing
     *
     * @return array
     */
    private function getBuiltInProviders()
    {
        return [new IdentityProvider()];
    }

    /**
     * returns a list of all the default processor methods
     *
     * @param  array  $providers - a list of all providers to build the processors with
     * @param  string $locale
     * @return array
     */
    private function getBuiltInProcessors(array $providers, $locale)
    {
        $this->fakerProcessorMethod = new Processor\Methods\Faker($providers, $locale);

        return [
            new Processor\Methods\Parameterized($this->parameterBag),
            new Processor\Methods\ArrayValue(),
            new Processor\Methods\Conditional(),
            $this->fakerProcessorMethod,
            new Processor\Methods\Reference(),
            new Processor\Methods\UnescapeAt(),
        ];
    }

    /**
     * returns a list of all the default parser methods
     *
     * @return array
     */
    private function getBuiltInParsers()
    {
        return [
            new Parser\Methods\Php($this),
            new Parser\Methods\Yaml($this),
        ];
    }

    /**
     * returns a list of all the default builder methods
     *
     * @return array
     */
    private function getBuiltInBuilders()
    {
        return [
            new Builder\Methods\RangeName(),
            new Builder\Methods\ListName(),
            new Builder\Methods\SimpleName(),
        ];
    }

    /**
     * returns a list of all the default instantiator methods
     *
     * @param  Processor\Processor $processor
     * @param  TypeHintChecker     $typeHintChecker
     * @return array
     */
    private function getBuiltInInstantiators(Processor\Processor $processor, TypeHintChecker $typeHintChecker)
    {
        return [
            new Instantiator\Methods\Unserialize(),
            new Instantiator\Methods\ReflectionWithoutConstructor(),
            new Instantiator\Methods\ReflectionWithConstructor($processor, $typeHintChecker),
            new Instantiator\Methods\EmptyConstructor(),
        ];
    }

    /**
     * returns a list of all the default populator methods
     *
     * @param  TypeHintChecker $typeHintChecker
     * @return array
     */
    private function getBuiltInPopulators(TypeHintChecker $typeHintChecker)
    {
        return [
            new Populator\Methods\ArrayAdd($typeHintChecker),
            new Populator\Methods\Custom(),
            new Populator\Methods\ArrayDirect($typeHintChecker),
            new Populator\Methods\Direct($typeHintChecker),
            new Populator\Methods\Property(),
            new Populator\Methods\MagicCall(),
        ];
    }
}
