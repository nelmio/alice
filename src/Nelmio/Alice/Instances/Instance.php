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

class Instance {

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
	protected $instantiators;
	
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

		$this->instantiators = array(
			new Instantiators\Unserialize(),
			new Instantiators\ReflectionWithoutConstructor(),
			new Instantiators\ReflectionWithConstructor($this->processor, $this->typeHintChecker),
			new Instantiators\EmptyConstructor(),
		);
	}

	public function asObject()
	{
		if (!is_null($this->object)) { return $this->object; }

		try {
			foreach ($this->instantiators as $instantiator) {
				if ($instantiator->canInstantiate($this->class, $this->spec)) {
					return $this->object = $instantiator->instantiate($this->class, $this->name, $this->spec);
				}
			}

			// exception otherwise
			throw new \RuntimeException('You must specify a __construct method with its arguments in object '.$this->name.' since class '.$this->class.' has mandatory constructor arguments');
		} catch (\ReflectionException $exception) {
			return $this->object = new $this->class();
		}
	}

}