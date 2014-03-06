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
		$instances = array();

		list($class, $classFlags) = $this->parseFlags($class);
		$enumItems = array_map('trim', explode(',', $this->matches[1]));
		foreach ($enumItems as $item) {
			$curSpec = $spec;
			$curName = str_replace($this->matches[0], $item, $name);
			list($curName, $instanceFlags) = $this->parseFlags($curName);
			$this->processor->setCurrentValue($item);
			$instance = new Instance(array($this->createInstance($class, $curName, $curSpec), $class, $curName, $curSpec, $classFlags, $instanceFlags, $item));
			$this->processor->unsetCurrentValue();
			$instances[] = $instance;
		}

		return $instances;
	}

}