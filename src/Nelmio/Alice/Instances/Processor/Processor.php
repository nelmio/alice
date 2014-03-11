<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor;

use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\Instances\PropertyDefinition;
use Nelmio\Alice\Instances\Processor\Methods;
use Nelmio\Alice\Instances\Processor\Processable;
use Nelmio\Alice\Instances\Processor\ProcessableInterface;

class Processor {

	function __construct(Collection $objects, array $providers, $locale = 'en_US') {
		$this->methods = array(
			new Methods\ArrayValue($this),
			new Methods\Conditional($this),
			new Methods\NonString(),
			new Methods\UnescapeAt(),
			new Methods\Faker($objects, $providers, $locale),
			new Methods\Reference($objects)
		);
	}

	public function setProviders(array $providers)
	{
		$this->fakerProcessor->setProviders($providers);
	}

	public function process($valueOrProcessable, array $variables, $valueForCurrent = null)
	{
		$value = $valueOrProcessable instanceof ProcessableInterface ? $valueOrProcessable->getValue() : $valueOrProcessable;

		if (!is_null($valueForCurrent)) { $this->valueForCurrent = $valueForCurrent; }

		foreach ($this->methods as $method) {
			$processable = new Processable($value);
			if ($method->canProcess($processable)) {
				if (method_exists($method, 'setValueForCurrent')) { $method->setValueForCurrent($this->valueForCurrent); }
				$value = $method->process($processable, $variables);
			}
		}
		
		if (!is_null($valueForCurrent)) { $this->valueForCurrent = null; }

		return $value;
	}

}