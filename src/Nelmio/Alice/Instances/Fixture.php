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

use Doctrine\Common\Collections\ArrayCollection;

use Nelmio\Alice\Instances\PropertyDefinition;
use Nelmio\Alice\Util\FlagParser;

class Fixture {
	
	/**
	 * @var string
	 */
	protected $class;
	
	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * @var array
	 */
	protected $spec;
	
	/**
	 * @var ArrayCollection
	 */
	protected $properties;
	
	/**
	 * @var array
	 */
	protected $classFlags;
	
	/**
	 * @var array
	 */
	protected $nameFlags;
	
	/**
	 * @var string
	 */
	protected $valueForCurrent;

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
	function __construct($class, $name, array $spec, $valueForCurrent) {
		list($this->class, $this->classFlags) = FlagParser::parse($class);
		list($this->name, $this->nameFlags)   = FlagParser::parse($name);
		
		$this->spec            = $spec;
		$this->valueForCurrent = $valueForCurrent;

		$this->properties = new ArrayCollection();
		foreach ($spec as $propertyName => $propertyValue) {
			$this->properties->set($propertyName, new PropertyDefinition($propertyName, $propertyValue));
		}
	}

	public function getClass()
	{
		return $this->class;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getProperties()
	{
		return $this->properties->filter(function($property) { return $property->isBasic(); });
	}

	public function getClassFlags()
	{
		return $this->classFlags;
	}

	public function getNameFlags()
	{
		return $this->nameFlags;
	}

	public function getValueForCurrent()
	{
		return $this->valueForCurrent;
	}

	public function getConstructorMethod()
	{
		return $this->getConstructorComponents()['method'];
	}

	public function getConstructorArgs()
	{
		return $this->getConstructorComponents()['args'];
	}

	public function shouldUseConstructor()
	{
		return !is_null($this->getConstructor()) && $this->getConstructor()->getValue();
	}

	public function hasCustomSetter()
	{
		return !is_null($this->getCustomSetter());
	}

	public function getCustomSetter()
	{
		return $this->properties->get('__set');
	}

	public function __toString()
	{
		return $this->getName();
	}

	protected function getConstructor()
	{
		return $this->properties->get('__construct');
	}

	//
	// Sequential arrays call the constructor, hashes call a static method
	//
	// array('foo', 'bar') => new $fixture->getClass()('foo', 'bar')
	// array('foo' => array('bar')) => $fixture->getClass()::foo('bar')
	//
	public function getConstructorComponents()
	{
		if (!is_array($this->getConstructor()->getValue())) {
			throw new \UnexpectedValueException("The __construct call in object '{$this}' must be defined as an array of arguments or false to bypass it");
		}

		list($method, $args) = each($this->getConstructor()->getValue());
		if ($method !== 0) {
			if (!is_callable(array($this->class, $method))) {
				throw new \UnexpectedValueException("Cannot call static method '{$method}' on class '{$this->class}' as a constructor for object '{$this}'");
			}
			if (!is_array($args)) {
				throw new \UnexpectedValueException("The static '{$method}' call in object '{$this}' must be given an array");
			}
			return array('method' => $method, 'args' => $args);	
		}
		return array('method' => '__construct', 'args' => $this->getConstructor()->getValue());
	}

}