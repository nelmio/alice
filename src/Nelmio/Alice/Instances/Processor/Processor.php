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
		$this->arrayProcessor       = new Methods\ArrayValue($this);
		$this->conditionalProcessor = new Methods\Conditional($this);
		$this->nonStringProcessor   = new Methods\NonString();
		$this->unescapeAtProcessor 	= new Methods\UnescapeAt();
		$this->fakerProcessor       = new Methods\Faker($objects, $providers, $locale);
		$this->referenceProcessor   = new Methods\Reference($objects);
	}

	public function setProviders(array $providers)
	{
		$this->fakerProcessor->setProviders($providers);
	}

	public function process($value, array $variables, $valueForCurrent = null)
	{
		$processable = $value instanceof ProcessableInterface ? $value : new Processable($value);

		if (!is_null($valueForCurrent)) {
			$this->valueForCurrent = $valueForCurrent;
		}
		if ($this->arrayProcessor->canProcess($processable)) {
			return $this->arrayProcessor->process($processable, $variables);
		}

		// check for conditional values (20%? true : false)
		if ($this->conditionalProcessor->canProcess($processable)) {
			return $this->conditionalProcessor->process($processable, $variables);
		}

		// return non-string values
		if ($this->nonStringProcessor->canProcess($processable)) {
			return $this->nonStringProcessor->process($processable, $variables);
		}
		
		$value = $processable->getValue();

		// format placeholders without preg_replace if there is only one to avoid __toString() being called
		$processable = new Processable($value);
		if ($this->fakerProcessor->canProcess($processable)) {
			$this->fakerProcessor->setValueForCurrent($this->valueForCurrent);
			$value = $this->fakerProcessor->process($processable, $variables);
		}

		// process references
		$processable = new Processable($value);
		if ($this->referenceProcessor->canProcess($processable)) {
			$value = $this->referenceProcessor->process($processable, $variables);
		}

		// unescape at-signs
		$processable = new Processable($value);
		if ($this->unescapeAtProcessor->canProcess($processable)) {
			$value = $this->unescapeAtProcessor->process($processable, $variables);
		}

		if (!is_null($valueForCurrent)) {
			$this->valueForCurrent = null;
		}

		return $value;
	}

}