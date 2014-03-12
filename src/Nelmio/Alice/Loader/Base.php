<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;

use Psr\Log\LoggerInterface;
use Nelmio\Alice\ORMInterface;
use Nelmio\Alice\LoaderInterface;
use Nelmio\Alice\Provider\IdentityProvider;
use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\Instances\Fixture;
use Nelmio\Alice\Instances\FixtureBuilder;
use Nelmio\Alice\Instances\Instantiator;
use Nelmio\Alice\Instances\Populator;
use Nelmio\Alice\Instances\Processor;
use Nelmio\Alice\Util\TypeHintChecker;

/**
 * Loads fixtures from an array or php file
 *
 * The php code if $data is a file has access to $loader->fake() to
 * generate data and must return an array of the format below.
 *
 * The array format must follow this example:
 *
 *     array(
 *         'Namespace\Class' => array(
 *             'name' => array(
 *                 'property' => 'value',
 *                 'property2' => 'value',
 *             ),
 *             'name2' => array(
 *                 [...]
 *             ),
 *         ),
 *     )
 */
class Base implements LoaderInterface
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
	 * @var FixtureBuilder
	 */
	protected $fixtureBuilder;

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
	 * @var ORMInterface
	 */
	protected $manager;

	/**
	 * @var callable|LoggerInterface
	 */
	private $logger;

	/**
	 * @param string $locale default locale to use with faker if none is
	 *      specified in the expression
	 * @param array $providers custom faker providers in addition to the default
	 *      ones from faker
	 * @param int $seed a seed to make sure faker generates data consistently across
	 *      runs, set to null to disable
	 */
	public function __construct($locale = 'en_US', array $providers = array(), $seed = 1)
	{
		$this->objects         = new Collection;
		$this->typeHintChecker = new TypeHintChecker;

		$this->fakerProcessorMethod = new Processor\Methods\Faker($this->objects, array_merge($this->getBuiltInProviders(), $providers), $locale);
		$processor = new Processor\Processor(array(
			new Processor\Methods\ArrayValue(),
			new Processor\Methods\Conditional(),
			new Processor\Methods\UnescapeAt(),
			$this->fakerProcessorMethod,
			new Processor\Methods\Reference($this->objects)
			));

		$this->fixtureBuilder = new FixtureBuilder\FixtureBuilder(array(
			new FixtureBuilder\Methods\RangeName(),
			new FixtureBuilder\Methods\ListName(),
			new FixtureBuilder\Methods\SimpleName()
			));

		$this->instantiator = new Instantiator\Instantiator(array(
			new Instantiator\Methods\Unserialize(),
			new Instantiator\Methods\ReflectionWithoutConstructor(),
			new Instantiator\Methods\ReflectionWithConstructor($processor, $this->typeHintChecker),
			new Instantiator\Methods\EmptyConstructor(),
			), $this->processor);

		$this->populator = new Populator\Populator($this->objects, $processor, array(
			new Populator\Methods\ArrayAdd($this->typeHintChecker),
			new Populator\Methods\Custom(),
			new Populator\Methods\ArrayDirect($this->typeHintChecker),
			new Populator\Methods\Direct($this->typeHintChecker),
			new Populator\Methods\Property()
			));

		if (is_numeric($seed)) {
			mt_srand($seed);
		}
	}

    private function getBuiltInProviders()
    {
        return array(new IdentityProvider());
    }

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function getReference($name, $property = null)
	{
		return $this->objects->find($name, $property);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getReferences()
	{
		return $this->objects->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setProviders(array $providers)
	{
		$this->fakerProcessorMethod->setProviders($providers);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setReferences(array $objects)
	{
		$this->objects->clear();
		foreach ($objects as $name => $object) {
			$this->objects->set($name, $object);
		}
	}

	/**
	 * parses a file at the given filename
	 *
	 * @param string filename
	 * @return array data
	 */
	protected function parseFile($filename)
	{
		$loader = $this;
		$includeWrapper = function() use ($filename, $loader) {
			ob_start();
			$res = include $filename;
			ob_end_clean();

			return $res;
		};

		$data = $includeWrapper();
		if (!is_array($data)) {
			throw new \UnexpectedValueException("Included file \"{$filename}\" must return an array of data");
		}
		return $data;
	}

	/**
	 * builds a collection of fixtures
	 *
	 * @param array $rawData
	 * @return Collection
	 */
	protected function buildFixtures(array $rawData)
	{
		$fixtures = array();

		foreach ($rawData as $class => $specs) {
			$this->log('Loading '.$class);
			foreach ($specs as $name => $spec) {
				$fixtures = array_merge($fixtures, $this->fixtureBuilder->build($class, $name, $spec));
			}
		}
		
		return $fixtures;
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
	 * @param array $fixtures
	 * @return array
	 */
	protected function populateObjects(array $fixtures)
	{
		$objects = array();
		
		foreach ($fixtures as $fixture) {
			$this->objects->set('self', $this->objects->get($fixture->getName()));
			$this->populator->populate($fixture);
			$this->objects->remove('self');
			
			// add the object in the object store unless it's local
			if (!isset($fixture->getClassFlags()['local']) && !isset($fixture->getNameFlags()['local'])) {
				$objects[$fixture->getName()] = $this->getReference($fixture->getName());
			}
		}

		return $objects;
	}

	public function setORM(ORMInterface $manager)
	{
		$this->manager = $manager;
		$this->typeHintChecker->setORM($manager);
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
}
