<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Builders;

use Nelmio\Alice\Instances\Builders\BuilderInterface;
use Nelmio\Alice\Instances\Instance;
use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\Instances\Processor;
use Nelmio\Alice\Util\FlagParser;
use Nelmio\Alice\Util\TypeHintChecker;

class BaseBuilder implements BuilderInterface {

	/**
	 * @var Collection
	 */
	protected $instances;

	function __construct(Collection $instances, Processor $processor, TypeHintChecker $typeHintChecker) {
		$this->instances       = $instances;
		$this->processor       = $processor;
		$this->typeHintChecker = $typeHintChecker;
	}

	/**
	 * tests whether this class can build an instance with the given name
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function canBuild($name)
	{
		return true;
	}

	/**
	 * builds an instance from the given class, name, and spec
	 *
	 * @param string $class
	 * @param string $name
	 * @param array $spec
	 * @return Instance|array
	 */
	public function build($class, $name, array $spec)
	{
		list($class, $classFlags)   = FlagParser::parse($class);
		list($name, $instanceFlags) = FlagParser::parse($name);
		$instance = new Instance(array($this->createInstance($class, $name, $spec), $class, $name, $spec, $classFlags, $instanceFlags, null));
		return $instance;
	}

	protected function createInstance($class, $name, array &$data)
	{
		try {
			// constructor is defined explicitly
			if (isset($data['__construct'])) {
				$args = $data['__construct'];
				unset($data['__construct']);

				// constructor override
				if (false === $args) {
					if (version_compare(PHP_VERSION, '5.4', '<')) {
						// unserialize hack for php <5.4
						return $this->instances->set($name, unserialize(sprintf('O:%d:"%s":0:{}', strlen($class), $class)));
					}

					$reflClass = new \ReflectionClass($class);

					return $this->instances->set($name, $reflClass->newInstanceWithoutConstructor());
				}

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
					throw new \UnexpectedValueException('The __construct call in object '.$name.' must be defined as an array of arguments or false to bypass it');
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

				return $this->instances->set($name, $instance);
			}

			// call the constructor if it contains optional params only
			$reflMethod = new \ReflectionMethod($class, '__construct');
			if (0 === $reflMethod->getNumberOfRequiredParameters()) {
				return $this->instances->set($name, new $class());
			}

			// exception otherwise
			throw new \RuntimeException('You must specify a __construct method with its arguments in object '.$name.' since class '.$class.' has mandatory constructor arguments');
		} catch (\ReflectionException $exception) {
			return $this->instances->set($name, new $class());
		}
	}

}