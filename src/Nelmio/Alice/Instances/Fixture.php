<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances;

use Nelmio\Alice\Instances\Instantiators;
use Nelmio\Alice\Instances\Processor;
use Nelmio\Alice\Util\FlagParser;
use Nelmio\Alice\Util\TypeHintChecker;

class Fixture {

	/**
	 * @var Processor
	 */
	protected $processor;

	/**
	 * @var array
	 */
	protected $instantiators;
	
	protected $object = null;
	public $class;
	public $name;
	protected $spec;
	public $classFlags;
	public $nameFlags;
	public $valueForCurrent;

	/**
	 * built a class representation of a fixture
	 *
	 * @param string $class
	 * @param string $name
	 * @param array $spec
	 * @param Processor $processor
	 * @param TypeHintChecker $typeHintChecker
	 * @param string $valueForCurrent - when <current()> is called, this value is used
	 */
	function __construct($class, $name, array $spec, Processor $processor, TypeHintChecker $typeHintChecker, $valueForCurrent=null) {
		list($this->class, $this->classFlags) = FlagParser::parse($class);
		list($this->name, $this->nameFlags)   = FlagParser::parse($name);
		
		$this->spec            = $spec;
		$this->valueForCurrent = $valueForCurrent;
		$this->processor       = $processor;

		$this->instantiators = array(
			new Instantiators\Unserialize(),
			new Instantiators\ReflectionWithoutConstructor(),
			new Instantiators\ReflectionWithConstructor($processor, $typeHintChecker),
			new Instantiators\EmptyConstructor(),
		);
	}

	public function constructorArgs()
	{
		return $this->spec['__construct'];
	}

	public function customSetter()
	{
		return $this->spec['__set'];
	}

	public function getPropertyMap()
	{
		$propertyMap = $this->spec;
		if (!is_null($this->constructorArgs())) { unset($propertyMap['__construct']); };
		if (!is_null($this->customSetter())) { unset($propertyMap['__set']); };
		return $propertyMap;
	}

	public function asObject()
	{
		if (!is_null($this->object)) { return $this->object; }

		try {
			foreach ($this->instantiators as $instantiator) {
				if ($instantiator->canInstantiate($this)) {
					$this->processor->setCurrentValue($this->valueForCurrent);
					$this->object = $instantiator->instantiate($this);
					$this->processor->unsetCurrentValue();
					return $this->object;
				}
			}

			// exception otherwise
			throw new \RuntimeException('You must specify a __construct method with its arguments in object '.$this->name.' since class '.$this->class.' has mandatory constructor arguments');
		} catch (\ReflectionException $exception) {
			return $this->object = new $this->class();
		}
	}

}