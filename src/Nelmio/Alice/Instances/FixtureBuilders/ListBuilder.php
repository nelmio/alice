<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\FixtureBuilders;

use Nelmio\Alice\Instances\Fixture;
use Nelmio\Alice\Util\FlagParser;

class ListBuilder extends BaseBuilder {

	private $matches = array();

	/**
	 * {@inheritDoc}
	 */
	public function canBuild($name)
	{
		return preg_match('#\{([^,]+(\s*,\s*[^,]+)*)\}#', $name, $this->matches);
	}

	/**
	 * {@inheritDoc}
	 */
	public function build($class, $name, array $spec)
	{
		$fixtures = array();

		$enumItems = array_map('trim', explode(',', $this->matches[1]));
		foreach ($enumItems as $itemName) {
			$currentName = str_replace($this->matches[0], $itemName, $name);
			$this->processor->setCurrentValue($itemName);
			$fixture = $this->newFixture($class, $currentName, $spec, $itemName);
			$this->processor->unsetCurrentValue();
			$fixtures[] = $fixture;
		}

		return $fixtures;
	}

}