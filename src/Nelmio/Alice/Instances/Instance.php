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

use Nelmio\Alice\Instances\Processor;
use Nelmio\Alice\Util\FlagParser;
use Nelmio\Alice\Util\TypeHintChecker;

class Instance {

	/**
	 * @var Processor
	 */
	protected $processor;

	/**
	 * @var TypeHintChecker
	 */
	protected $typeHintChecker;
	
	protected $object = null;
	public $class;
	public $name;
	public $spec;
	public $classFlags;
	public $nameFlags;
	public $valueForCurrent;

	/**
	 * 
	 */
	function __construct($class, $name, array $spec, Processor $processor, TypeHintChecker $typeHintChecker, $valueForCurrent=null) {
		list($this->class, $this->classFlags) = FlagParser::parse($class);
		list($this->name, $this->nameFlags)   = FlagParser::parse($name);
		
		$this->spec            = $spec;
		$this->valueForCurrent = $valueForCurrent;
		$this->processor       = $processor;
		$this->typeHintChecker = $typeHintChecker;
	}

	public function asObject()
	{
		if (!is_null($this->object)) { return $this->object; }

		try {
			// constructor is defined explicitly
			if (isset($this->spec['__construct'])) {
				$args = $this->spec['__construct'];
				unset($this->spec['__construct']);

				// constructor override
				if ($args === false) {
					return version_compare(PHP_VERSION, '5.4', '<') ? $this->instantiateByUnserialize() : $this->instantiateByReflectionWithoutConstructor();
				}

				return $this->instantiateByReflectionWithConstructor($args);
			}

			// call the constructor if it contains optional params only
			$reflMethod = new \ReflectionMethod($this->class, '__construct');
			if (0 === $reflMethod->getNumberOfRequiredParameters()) {
				return $this->instantiateByEmptyConstructor();
			}

			// exception otherwise
			throw new \RuntimeException('You must specify a __construct method with its arguments in object '.$name.' since class '.$this->class.' has mandatory constructor arguments');
		} catch (\ReflectionException $exception) {
			return $this->instantiateByEmptyConstructor();
		}
	}

	private function instantiateByUnserialize()
	{
		// unserialize hack for php <5.4
		return $this->object = unserialize(sprintf('O:%d:"%s":0:{}', strlen($this->class), $this->class));
	}

	private function instantiateByReflectionWithoutConstructor()
	{
		$reflClass = new \ReflectionClass($this->class);
		return $this->object = $reflClass->newInstanceWithoutConstructor();
	}

	private function instantiateByReflectionWithConstructor($args)
	{
		//
		// Sequential arrays call the constructor, hashes call a static method
		//
		// array('foo', 'bar') => new $this->class('foo', 'bar')
		// array('foo' => array('bar')) => $this->class::foo('bar')
		//
		if (is_array($args)) {
			$constructor = '__construct';
			list($index, $values) = each($args);
			if ($index !== 0) {
				if (!is_array($values)) {
					throw new \UnexpectedValueException("The static '$index' call in object '$name' must be given an array");
				}
				if (!is_callable(array($this->class, $index))) {
					throw new \UnexpectedValueException("Cannot call static method '$index' on class '$this->class' as a constructor for object '$name'");
				}
				$constructor = $index;
				$args = $values;
			}
		} else {
			throw new \UnexpectedValueException('The __construct call in object '.$this->name.' must be defined as an array of arguments or false to bypass it');
		}

				// create object with given args
		$reflClass = new \ReflectionClass($this->class);
		$args = $this->processor->process($args, array());
		foreach ($args as $num => $param) {
			$args[$num] = $this->typeHintChecker->check($this->class, $constructor, $param, $num);
		}

		if ($constructor === '__construct') {
			$instance = $reflClass->newInstanceArgs($args);
		} else {
			$instance = forward_static_call_array(array($this->class, $constructor), $args);
			if (!($instance instanceof $this->class)) {
				throw new \UnexpectedValueException("The static constructor '$constructor' for object '$name' returned an object that is not an instance of '$this->class'");
			}
		}

		return $this->object = $instance;
	}

	private function instantiateByEmptyConstructor()
	{
		return $this->object = new $this->class();
	}

}