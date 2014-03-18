<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Populator\Methods;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Populator\Methods\MethodInterface;

class Custom implements MethodInterface {

	/**
	 * {@inheritDoc}
	 */
	public function canSet(Fixture $fixture, $object, $property, $value)
	{
		return $fixture->hasCustomSetter();
	}

	/**
	 * {@inheritDoc}
	 */
	public function set(Fixture $fixture, $object, $property, $value)
	{
		if (!method_exists($object, $fixture->getCustomSetter())) {
			throw new \RuntimeException('Setter ' . $fixture->getCustomSetter() . ' not found in object');
		}
		$customSetter = $fixture->getCustomSetter()->getValue();
		$object->$customSetter($property, $value);
	}

}