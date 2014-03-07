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

class Unserialize {

	public function canInstantiate($class, array $spec)
	{
		return isset($spec['__construct']) && $spec['__construct'] === false && version_compare(PHP_VERSION, '5.4', '<');
	}

	public function instantiate($class, $name, array &$spec)
	{
		unset($spec['__construct']);
		// unserialize hack for php <5.4
		return unserialize(sprintf('O:%d:"%s":0:{}', strlen($class), $class));
	}

}