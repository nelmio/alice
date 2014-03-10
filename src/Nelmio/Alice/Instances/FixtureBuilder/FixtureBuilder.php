<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\FixtureBuilder;

class FixtureBuilder {

	/**
	 * @var array
	 **/
	protected $methods;

	function __construct(array $methods) {
		$this->methods = $methods;
	}

	/**
	 * builds a single fixture from a "raw" definition
	 *
	 * @param array $rawData
	 */
	public function build($class, $name, array $spec)
	{
		foreach ($this->methods as $method) {
			if ($method->canBuild($name)) {
				return $method->build($class, $name, $spec);
			}
		}
	}
}