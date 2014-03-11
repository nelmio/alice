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

	/**
	 * @var Collection
	 */
	protected $objects;

	function __construct(Collection $objects, array $providers, $locale = 'en_US') {
		$this->objects       = $objects;

		$this->arrayProcessor       = new Methods\ArrayValue($this);
		$this->conditionalProcessor = new Methods\Conditional($this);
		$this->nonStringProcessor   = new Methods\NonString();
		$this->unescapeAtProcessor 	= new Methods\UnescapeAt();
		$this->fakerProcessor       = new Methods\Faker($objects, $providers, $locale);
	}

	public function setProviders(array $providers)
	{
		$this->providers = $providers;
		$this->generators = array();
	}

	public function process($processable, array $variables, $valueForCurrent = null)
	{
		$processable = $processable instanceof ProcessableInterface ? $processable : new Processable($processable);

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
		if ($this->fakerProcessor->canProcess($processable)) {
			$this->fakerProcessor->setValueForCurrent($this->valueForCurrent);
			$value = $this->fakerProcessor->process($processable, $variables);
		}

		// process references
		if (is_string($value) && preg_match('{^(?:(?<multi>\d+)x )?@(?<reference>[a-z0-9_.*-]+)(?:\->(?<property>[a-z0-9_-]+))?$}i', $value, $matches)) {
			$multi    = ('' !== $matches['multi']) ? $matches['multi'] : null;
			$property = isset($matches['property']) ? $matches['property'] : null;
			if (strpos($matches['reference'], '*')) {
				$value = $this->objects->random($matches['reference'], $multi, $property);
			} else {
				if (null !== $multi) {
					throw new \UnexpectedValueException('To use multiple references you must use a mask like "'.$matches['multi'].'x @user*", otherwise you would always get only one item.');
				}
				$value = $this->objects->find($matches['reference'], $property);
			}
		}

		// unescape at-signs
		if ($this->unescapeAtProcessor->canProcess(new Processable($value))) {
			$value = $this->unescapeAtProcessor->process(new Processable($value), $variables);
		}

		if (!is_null($valueForCurrent)) {
			$this->valueForCurrent = null;
		}

		return $value;
	}

}