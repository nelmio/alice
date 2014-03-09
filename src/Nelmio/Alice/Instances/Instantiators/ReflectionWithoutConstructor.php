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

class ReflectionWithoutConstructor {

	public function canInstantiate(Fixture $fixture)
	{
		return !is_null($fixture->getConstructorArgs()) && $fixture->getConstructorArgs() === false && !version_compare(PHP_VERSION, '5.4', '<');
	}

	public function instantiate(Fixture $fixture)
	{
		$reflClass = new \ReflectionClass($fixture->class);
		return $reflClass->newInstanceWithoutConstructor();
	}

}