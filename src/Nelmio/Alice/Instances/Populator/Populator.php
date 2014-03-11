<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Populator;

use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\Instances\Fixture;
use Nelmio\Alice\Instances\PropertyDefinition;
use Nelmio\Alice\Instances\Populator\Methods;
use Nelmio\Alice\Instances\Processor\Processor;
use Nelmio\Alice\Util\TypeHintChecker;

class Populator {
	
	/**
	* @var Collection
	*/
	protected $objects;

	/**
	 * @var Processor
	 */
	protected $processor;

	/**
	 * @var array
	 */
	private $uniqueValues = array();

	function __construct(Collection $objects, Processor $processor, TypeHintChecker $typeHintChecker) {
		$this->objects         = $objects;
		$this->processor       = $processor;

		$this->arrayAddSetter    = new Methods\ArrayAdd($typeHintChecker);
		$this->customSetter      = new Methods\Custom();
		$this->arrayDirectSetter = new Methods\ArrayDirect($typeHintChecker);
		$this->directSetter      = new Methods\Direct($typeHintChecker);
		$this->propertySetter    = new Methods\Property();
	}

	/**
	 * populate all the properties for the object described by the given fixture
	 *
	 * @param Fixture $fixture
	 */
	public function populate(Fixture $fixture)
	{
		$class  = $fixture->getClass();
		$name   = $fixture->getName();
		$object = $this->objects->get($name);

		$variables = array();

		foreach ($fixture->getProperties() as $property) {
			$key = $property->getName();
			$val = $property->getValue();

			if (is_array($val) && '{' === key($val)) {
				throw new \RuntimeException('Misformatted string in object '.$name.', '.$key.'\'s value should be quoted if you used yaml');
			}

			$value = $property->requiresUnique() ? 
				$this->generateUnique($fixture, $property, $variables) : 
				$this->processor->process($property, $variables, $fixture->getValueForCurrent());

			if ($this->arrayAddSetter->canSet($fixture, $object, $key, $value)) {
				$this->arrayAddSetter->set($fixture, $object, $key, $value);
			} 
			elseif ($this->customSetter->canSet($fixture, $object, $key, $value)) {
				$this->customSetter->set($fixture, $object, $key, $value);
				$variables[$key] = $value;
			} 
			elseif ($this->arrayDirectSetter->canSet($fixture, $object, $key, $value)) {
				$this->arrayDirectSetter->set($fixture, $object, $key, $value);
				$variables[$key] = $value;
			} 
			elseif ($this->directSetter->canSet($fixture, $object, $key, $value)) {
				$this->directSetter->set($fixture, $object, $key, $value);
				$variables[$key] = $value;
			} 
			elseif ($this->propertySetter->canSet($fixture, $object, $key, $value)) {
				$this->propertySetter->set($fixture, $object, $key, $value);
				$variables[$key] = $value;
			} 
			else {
				throw new \UnexpectedValueException('Could not determine how to assign '.$key.' to a '.$class.' object');
			}
		}
	}

	protected function generateUnique(Fixture $fixture, PropertyDefinition $property, array $variables)
	{
		$class = $fixture->getClass();
		$key = $property->getName();
		$i = $uniqueTriesLimit = 128;

		do {
			// process values
			$value = $this->processor->process($property, $variables, $fixture->getValueForCurrent());

			if (is_object($value)) {
				$valHash = spl_object_hash($value);
			} elseif (is_array($value)) {
				$valHash = hash('md4', serialize($value));
			} else {
				$valHash = $value;
			}
		} while (--$i > 0 && isset($this->uniqueValues[$class . $key][$valHash]));

		if (isset($this->uniqueValues[$class . $key][$valHash])) {
			throw new \RuntimeException("Couldn't generate random unique value for $class: $key in $uniqueTriesLimit tries.");
		}

		$this->uniqueValues[$class . $key][$valHash] = true;
		return $value;
	}

}