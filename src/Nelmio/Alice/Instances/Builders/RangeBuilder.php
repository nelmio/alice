<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Builders;

use Nelmio\Alice\Instances\Instance;
use Nelmio\Alice\Util\FlagParser;

class RangeBuilder extends BaseBuilder {

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
		$instances = array();

		list($class, $classFlags) = FlagParser::parse($class);
		$from = $this->matches[1];
		$to = empty($this->matches[2]) ? $this->matches[3] : $this->matches[3] - 1;
		if ($from > $to) {
			list($to, $from) = array($from, $to);
		}
		for ($i = $from; $i <= $to; $i++) {
			$curSpec = $spec;
			$curName = str_replace($this->matches[0], $i, $name);
			list($curName, $instanceFlags) = FlagParser::parse($curName);
			$this->processor->setCurrentValue($i);
			$instance = new Instance(array($this->createInstance($class, $curName, $curSpec), $class, $curName, $curSpec, $classFlags, $instanceFlags, $i));
			$this->processor->unsetCurrentValue();
			$instances[] = $instance;
		}

		return $instances;
	}

}