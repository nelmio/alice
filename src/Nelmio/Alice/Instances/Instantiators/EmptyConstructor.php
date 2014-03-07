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

class EmptyConstructor {

	public function canInstantiate($class, array $spec)
	{
		return (new \ReflectionMethod($class, '__construct'))->getNumberOfRequiredParameters() === 0;
	}

	public function instantiate($class, $name, array &$spec)
	{
		return new $class();
	}

}