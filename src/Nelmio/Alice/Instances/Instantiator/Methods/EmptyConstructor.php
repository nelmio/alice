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

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Instantiator\Methods\MethodInterface;

class EmptyConstructor implements MethodInterface {

	/**
	 * {@inheritDoc}
	 */
	public function canInstantiate(Fixture $fixture)
	{
		return (new \ReflectionMethod($fixture->getClass(), '__construct'))->getNumberOfRequiredParameters() === 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function instantiate(Fixture $fixture)
	{
		$class = $fixture->getClass();
		return new $class();
	}

}