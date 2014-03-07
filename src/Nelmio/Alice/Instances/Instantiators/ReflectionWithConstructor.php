<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiators;

use Nelmio\Alice\Instances\Processor;
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

	public function canInstantiate($class, array $spec)
	{
		return isset($spec['__construct']) && $spec['__construct'];
	}

	public function instantiate($class, $name, array &$spec)
	{
		$args = $spec['__construct'];
		unset($spec['__construct']);

		//
		// Sequential arrays call the constructor, hashes call a static method
		//
		// array('foo', 'bar') => new $class('foo', 'bar')
		// array('foo' => array('bar')) => $class::foo('bar')
		//
		if (is_array($args)) {
			$constructor = '__construct';
			list($index, $values) = each($args);
			if ($index !== 0) {
				if (!is_array($values)) {
					throw new \UnexpectedValueException("The static '$index' call in object '$name' must be given an array");
				}
				if (!is_callable(array($class, $index))) {
					throw new \UnexpectedValueException("Cannot call static method '$index' on class '$class' as a constructor for object '$name'");
				}
				$constructor = $index;
				$args = $values;
			}
		} else {
			throw new \UnexpectedValueException("The __construct call in object '$name' must be defined as an array of arguments or false to bypass it");
		}

				// create object with given args
		$reflClass = new \ReflectionClass($class);
		$args = $this->processor->process($args, array());
		foreach ($args as $num => $param) {
			$args[$num] = $this->typeHintChecker->check($class, $constructor, $param, $num);
		}

		if ($constructor === '__construct') {
			$instance = $reflClass->newInstanceArgs($args);
		} else {
			$instance = forward_static_call_array(array($class, $constructor), $args);
			if (!($instance instanceof $class)) {
				throw new \UnexpectedValueException("The static constructor '$constructor' for object '$name' returned an object that is not an instance of '$class'");
			}
		}

		return $instance;
	}

}