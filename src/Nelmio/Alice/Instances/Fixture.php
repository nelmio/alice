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
	 * @var array
	 */
	protected $setProperties = array();

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
			$this->addProperty($propertyName, $propertyValue);
		}
	}

	/**
	 * returns true when the fixture has been flagged as a template
	 */
	public function isTemplate()
	{
		return $this->hasNameFlag('template');
	}

	/**
	 * extends this fixture by the given template
	 *
	 * @param Fixture $template
	 */
	public function extendTemplate(Fixture $template)
	{
		if (!$template->isTemplate()) { throw new \InvalidArgumentException('Argument must be a template, not just a fixture.'); }

		foreach ($template->properties as $property) {
			if (!isset($this->spec[$property->getName()])) {
				$this->addProperty($property->getName(), $property->getValue());
			}
		}
	}

	/**
	 * returns a list of templates to extend
	 *
	 * @return array
	 */
	public function getExtensions()
	{
		$extensions = array_filter(array_keys($this->nameFlags), function($flag) {
			return preg_match('#^extends\s*(.+)$#', $flag);
		});

		return array_map(function($extension) {
			return str_replace('extends ', '', $extension);
		}, $extensions);
	}

	/**
	 * returns true if the fixture has extensions
	 *
	 * @return boolean
	 */
	public function hasExtensions()
	{
		return count($this->getExtensions()) > 0;
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

	/**
	 * get the list of class flags on this fixture
	 *
	 * @return array
	 */
	public function getClassFlags()
	{
		return $this->classFlags;
	}

	/**
	 * returns true if this fixture has the given class flag
	 *
	 * @return boolean
	 */
	public function hasClassFlag($flag)
	{
		return in_array($flag, array_keys($this->classFlags));
	}

	/**
	 * get the list of name flags on this fixture
	 *
	 * @return array
	 */
	public function getNameFlags()
	{
		return $this->nameFlags;
	}

	/**
	 * returns true if this fixture has the given name flag
	 *
	 * @return boolean
	 */
	public function hasNameFlag($flag)
	{
		return in_array($flag, array_keys($this->nameFlags));
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

	public function setPropertyValue($property, $value)
	{
		$this->setProperties[$property] = $value;
	}

	public function getPropertyValue($property)
	{
		return $this->setProperties[$property];
	}

	public function getSetProperties()
	{
		return $this->setProperties;
	}

	public function __toString()
	{
		return $this->getName();
	}

	/**
	 * creates and adds a PropertyDefinition to the fixture with the given name and value
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	protected function addProperty($name, $value)
	{
		$this->properties->set($name, new PropertyDefinition($name, $value));
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
	protected function getConstructorComponents()
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