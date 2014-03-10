<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\FixtureBuilder\Methods;

use Nelmio\Alice\Instances\Fixture;
use Nelmio\Alice\Instances\FixtureBuilder\Methods\MethodInterface;
use Nelmio\Alice\Instances\Processor;

class SimpleName implements MethodInterface {

	/**
	 * {@inheritDoc}
	 */
	public function canBuild($name)
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function build($class, $name, array $spec)
	{
		return array(new Fixture($class, $name, $spec, null));
	}

}