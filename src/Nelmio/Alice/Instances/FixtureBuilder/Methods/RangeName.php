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

class RangeName implements MethodInterface {

	private $matches = array();

	/**
	 * @var Processor
	 */
	protected $processor;

	function __construct(Processor $processor) {
		$this->processor = $processor;
	}

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
			$this->processor->setCurrentValue($currentIndex);
			$fixture = new Fixture($class, $currentName, $spec, $currentIndex);
			$this->processor->unsetCurrentValue();
			$fixtures[] = $fixture;
		}

		return $fixtures;
	}

}