<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances;

class Instance {
	
	public $object;
	public $class;
	public $name;
	public $spec;
	public $classFlags;
	public $instanceFlags;
	public $currentValue;

	function __construct(array $values) {
		$this->object        = $values[0];
		$this->class         = $values[1];
		$this->name          = $values[2];
		$this->spec          = $values[3];
		$this->classFlags    = $values[4];
		$this->instanceFlags = $values[5];
		$this->currentValue  = $values[6];
	}

}