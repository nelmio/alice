<?php

namespace Nelmio\Alice\support\extensions;

use Nelmio\Alice\Instances\Fixture;
use Nelmio\Alice\Instances\FixtureBuilder\Methods\MethodInterface as BuilderInterface;

class CustomBuilder implements BuilderInterface {

	public function canBuild($name)
	{
		return $name == 'spec dumped';
	}

	/**
	 * this custom builder dumps the given spec
	 */
	public function build($class, $name, array $spec)
	{
		return array(new Fixture($class, $name, array(), null));
	}

}