<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator;

use Nelmio\Alice\Instances\Fixture;
use Nelmio\Alice\Instances\Processor\Processor;

class Instantiator {

	/**
	 * @var array
	 **/
	protected $methods;

	/**
	 * @var Processor
	 */
	protected $processor;

	function __construct(array $methods, Processor $processor) {
		$this->methods   = $methods;
		$this->processor = $processor;
	}

	public function instantiate(Fixture $fixture)
	{
		try {
			foreach ($this->methods as $method) {
				if ($method->canInstantiate($fixture)) {
					return $method->instantiate($fixture);
				}
			}

      // exception otherwise
			throw new \RuntimeException("You must specify a __construct method with its arguments in object '{$fixture->getName()}' since class '{$fixture->getClass()}' has mandatory constructor arguments");
		} catch (\ReflectionException $exception) {
			$class = $fixture->getClass();
			return new $class();
		}
	}

}