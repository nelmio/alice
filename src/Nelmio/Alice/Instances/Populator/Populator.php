<?php

namespace Nelmio\Alice\Instances\Populator;

use Symfony\Component\Form\Util\FormUtil;
use Symfony\Component\PropertyAccess\StringUtil;

use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\Instances\Fixture;
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
	 * @var TypeHintChecker
	 */
	protected $typeHintChecker;

	/**
	 * @var array
	 */
	private $uniqueValues = array();

	function __construct(Collection $objects, Processor $processor, TypeHintChecker $typeHintChecker) {
		$this->objects = $objects;
		$this->processor = $processor;
		$this->typeHintChecker = $typeHintChecker;
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

		if ($fixture->hasCustomSetter()) {
			if (!method_exists($object, $fixture->getCustomSetter())) {
				throw new \RuntimeException('Setter ' . $fixture->getCustomSetter() . ' not found in object');
			}
			$customSetter = $fixture->getCustomSetter()->getValue();
		}

		foreach ($fixture->getProperties() as $property) {
			$key = $property->getName();
			$val = $property->getValue();

			if (is_array($val) && '{' === key($val)) {
				throw new \RuntimeException('Misformatted string in object '.$name.', '.$key.'\'s value should be quoted if you used yaml');
			}

			if (isset($property->getNameFlags()['unique'])) {
				$i = $uniqueTriesLimit = 128;

				do {
										// process values
					$generatedVal = $this->processor->process($property, $variables, $fixture->getValueForCurrent());

					if (is_object($generatedVal)) {
						$valHash = spl_object_hash($generatedVal);
					} elseif (is_array($generatedVal)) {
						$valHash = hash('md4', serialize($generatedVal));
					} else {
						$valHash = $generatedVal;
					}
				} while (--$i > 0 && isset($this->uniqueValues[$class . $key][$valHash]));

				if (isset($this->uniqueValues[$class . $key][$valHash])) {
					throw new \RuntimeException("Couldn't generate random unique value for $class: $key in $uniqueTriesLimit tries.");
				}

				$this->uniqueValues[$class . $key][$valHash] = true;
			} else {
				$generatedVal = $this->processor->process($property, $variables, $fixture->getValueForCurrent());
			}

						// add relations if available
			if (is_array($generatedVal) && $method = $this->findAdderMethod($object, $key)) {
				foreach ($generatedVal as $rel) {
					$rel = $this->typeHintChecker->check($object, $method, $rel);
					$object->{$method}($rel);
				}
			} elseif (isset($customSetter)) {
				$object->$customSetter($key, $generatedVal);
				$variables[$key] = $generatedVal;
			} elseif (is_array($generatedVal) && method_exists($object, $key)) {
				foreach ($generatedVal as $num => $param) {
					$generatedVal[$num] = $this->typeHintChecker->check($object, $key, $param, $num);
				}
				call_user_func_array(array($object, $key), $generatedVal);
				$variables[$key] = $generatedVal;
			} elseif (method_exists($object, 'set'.$key)) {
				$generatedVal = $this->typeHintChecker->check($object, 'set'.$key, $generatedVal);
				if(!is_callable(array($object, 'set'.$key))) {
					$refl = new \ReflectionMethod($object, 'set'.$key);
					$refl->setAccessible(true);
					$refl->invoke($object, $generatedVal);
				} else {
					$object->{'set'.$key}($generatedVal);
				}
				$variables[$key] = $generatedVal;
			} elseif (property_exists($object, $key)) {
				$refl = new \ReflectionProperty($object, $key);
				$refl->setAccessible(true);
				$refl->setValue($object, $generatedVal);

				$variables[$key] = $generatedVal;
			} else {
				throw new \UnexpectedValueException('Could not determine how to assign '.$key.' to a '.$class.' object');
			}
		}
	}

	private function findAdderMethod($obj, $key)
	{
		if (method_exists($obj, $method = 'add'.$key)) {
			return $method;
		}

		if (class_exists('Symfony\Component\PropertyAccess\StringUtil') && method_exists('Symfony\Component\PropertyAccess\StringUtil', 'singularify')) {
			foreach ((array) StringUtil::singularify($key) as $singularForm) {
				if (method_exists($obj, $method = 'add'.$singularForm)) {
					return $method;
				}
			}
		} elseif (class_exists('Symfony\Component\Form\Util\FormUtil') && method_exists('Symfony\Component\Form\Util\FormUtil', 'singularify')) {
			foreach ((array) FormUtil::singularify($key) as $singularForm) {
				if (method_exists($obj, $method = 'add'.$singularForm)) {
					return $method;
				}
			}
		}

		if (method_exists($obj, $method = 'add'.rtrim($key, 's'))) {
			return $method;
		}

		if (substr($key, -3) === 'ies' && method_exists($obj, $method = 'add'.substr($key, 0, -3).'y')) {
			return $method;
		}
	}

}