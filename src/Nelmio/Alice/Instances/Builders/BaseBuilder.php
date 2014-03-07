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

use Nelmio\Alice\Instances\Builders\BuilderInterface;
use Nelmio\Alice\Instances\Instance;
use Nelmio\Alice\Instances\Processor;
use Nelmio\Alice\Util\TypeHintChecker;

class BaseBuilder implements BuilderInterface {

	/**
	 * @var Processor
	 */
	protected $processor;

	/**
	 * @var TypeHintChecker
	 */
	protected $typeHintChecker;

	function __construct(Processor $processor, TypeHintChecker $typeHintChecker) {
		$this->processor       = $processor;
		$this->typeHintChecker = $typeHintChecker;
	}

	/**
	 * {@inheritDoc}
	 */
	public function canBuild($name)
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function build($class, $name, array $spec)
	{
		return new Instance($class, $name, $spec, $this->processor, $this->typeHintChecker);
	}

}