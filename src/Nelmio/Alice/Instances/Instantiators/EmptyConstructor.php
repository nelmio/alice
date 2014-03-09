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

use Nelmio\Alice\Instances\Fixture;

class EmptyConstructor {

	public function canInstantiate(Fixture $fixture)
	{
		return (new \ReflectionMethod($fixture->class, '__construct'))->getNumberOfRequiredParameters() === 0;
	}

	public function instantiate($fixture)
	{
		return new $fixture->class();
	}

}