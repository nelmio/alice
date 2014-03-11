<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor\Methods;

use Nelmio\Alice\Instances\Processor\Processor;
use Nelmio\Alice\Instances\Processor\ProcessableInterface;

class NonString {

	/**
	 * {@inheritDoc}
	 */
	public function canProcess(ProcessableInterface $processable)
	{
		return !is_string($processable->getValue());
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(ProcessableInterface $processable, array $variables)
	{
		return $processable->getValue();
	}

}