<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Methods;

use Nelmio\Alice\Instances\Fixture;
use Nelmio\Alice\Instances\Processor\Processor;
use Nelmio\Alice\Util\TypeHintChecker;

class ReflectionWithConstructor {

	/**
	 * @var Processor
	 */
	protected $processor;

	/**
	 * @var TypeHintChecker
	 */
	protected $typeHintChecker;

	function __construct(Processor $processor, TypeHintChecker $typeHintChecker) {
		$this->processor       = $processor;
		$this->typeHintChecker = $typeHintChecker;
	}

	public function canInstantiate(Fixture $fixture)
	{
		return !is_null($fixture->getConstructor()) && $fixture->getConstructor()->getValue();
	}

	public function instantiate(Fixture $fixture)
	{
		$args = $fixture->getConstructor()->getValue();

		//
		// Sequential arrays call the constructor, hashes call a static method
		//
		// array('foo', 'bar') => new $fixture->getClass()('foo', 'bar')
		// array('foo' => array('bar')) => $fixture->getClass()::foo('bar')
		//
		if (is_array($args)) {
			$constructor = '__construct';
			list($index, $values) = each($args);
			if ($index !== 0) {
				if (!is_array($values)) {
					throw new \UnexpectedValueException("The static '$index' call in object '{$fixture}' must be given an array");
				}
				if (!is_callable(array($fixture->getClass(), $index))) {
					throw new \UnexpectedValueException("Cannot call static method '$index' on class '{$fixture->getClass()}' as a constructor for object '{$fixture}'");
				}
				$constructor = $index;
				$args = $values;
			}
		} else {
			throw new \UnexpectedValueException("The __construct call in object '{$fixture}' must be defined as an array of arguments or false to bypass it");
		}

		// create object with given args
		$reflClass = new \ReflectionClass($fixture->getClass());
		
		$this->processor->setCurrentValue($fixture->getValueForCurrent());
		$args = $this->processor->parse($args, array());
		$this->processor->unsetCurrentValue();
		
		foreach ($args as $num => $param) {
			$args[$num] = $this->typeHintChecker->check($fixture->getClass(), $constructor, $param, $num);
		}

		if ($constructor === '__construct') {
			$instance = $reflClass->newInstanceArgs($args);
		} else {
			$instance = forward_static_call_array(array($fixture->getClass(), $constructor), $args);
			$class = $fixture->getClass();
			if (!($instance instanceof $class)) {
				throw new \UnexpectedValueException("The static constructor '$constructor' for object '{$fixture}' returned an object that is not an instance of '{$fixture->getClass()}'");
			}
		}

		return $instance;
	}

}