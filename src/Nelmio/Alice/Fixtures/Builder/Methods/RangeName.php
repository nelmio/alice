<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder\Methods;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Fixtures\Builder\Methods\MethodInterface;

class RangeName implements MethodInterface {

	private $matches = array();

	/**
	 * {@inheritDoc}
	 */
	public function canBuild($name)
	{
		return preg_match('#\{([0-9]+)\.\.(\.?)([0-9]+)\}#i', $name, $this->matches);
	}

	/**
	 * {@inheritDoc}
	 */
	public function build($class, $name, array $spec)
	{
		$fixtures = array();

		$from = $this->matches[1];
		$to = empty($this->matches[2]) ? $this->matches[3] : $this->matches[3] - 1;
		if ($from > $to) {
			list($to, $from) = array($from, $to);
		}
		for ($currentIndex = $from; $currentIndex <= $to; $currentIndex++) {
			$currentName = str_replace($this->matches[0], $currentIndex, $name);
			$fixture = new Fixture($class, $currentName, $spec, $currentIndex);
			$fixtures[] = $fixture;
		}

		return $fixtures;
	}

}