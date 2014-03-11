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

	/**
	 * Custom faker providers to use with faker generator
	 *
	 * @var array
	 */
	private $providers;

	/**
	 * @var \Faker\Generator[]
	 */
	private $generators;

	/**
	 * Default locale to use with faker
	 *
	 * @var string
	 */
	private $defaultLocale;

	/**
	 * @var int
	 */
	private $currentValue;

	function __construct($locale = 'en_US', Collection $objects, array $providers) {
		$this->defaultLocale = $locale;
		$this->objects       = $objects;
		$this->providers     = $providers;

		$this->arrayProcessor       = new Methods\ArrayValue($this);
		$this->conditionalProcessor = new Methods\Conditional($this);
		$this->nonStringProcessor   = new Methods\NonString();
		$this->unescapeAtProcessor 	= new Methods\UnescapeAt();
	}

	public function setProviders(array $providers)
	{
		$this->providers = $providers;
		$this->generators = array();
	}

	public function setCurrentValue($value)
	{
		$this->currentValue = $value;
	}

	public function unsetCurrentValue()
	{
		$this->currentValue = null;
	}

	public function process($processable, array $variables)
	{
		$processable = $processable instanceof ProcessableInterface ? $processable : new Processable($processable);

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
		$placeHolderRegex = '<(?:(?<locale>[a-z]+(?:_[a-z]+)?):)?(?<name>[a-z0-9_]+?)\((?<args>(?:[^)]*|\)(?!>))*)\)>';
		if (preg_match('#^'.$placeHolderRegex.'$#i', $value, $matches)) {
			$value = $this->replacePlaceholder($matches, $variables);
		} else {
			// format placeholders inline
			$that = $this;
			$value = preg_replace_callback('#'.$placeHolderRegex.'#i', function ($matches) use ($that, $variables) {
				return $that->replacePlaceholder($matches, $variables);
			}, $value);
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

		return $value;
	}

	/**
	 * replaces a placeholder by the result of a ->fake call
	 */ 
	private function replacePlaceholder($matches, array $variables) {
		$args = isset($matches['args']) && '' !== $matches['args'] ? $matches['args'] : null;

		if (!$args) {
			return $this->fake($matches['name'], $matches['locale']);
		}

		// replace references to other variables in the same object
		$args = preg_replace_callback('{\{?\$([a-z0-9_]+)\}?}i', function ($match) use ($variables) {
			if (array_key_exists($match[1], $variables)) {
				return '$variables['.var_export($match[1], true).']';
			}

			return $match[0];
		}, $args);

		// replace references to other objects
		$args = preg_replace_callback('{(?:\b|^)(?:(?<multi>\d+)x )?(?<!\\\\)@(?<reference>[a-z0-9_.*-]+)(?:\->(?<property>[a-z0-9_-]+))?(?:\b|$)}i', function ($match) {
			$multi    = ('' !== $match['multi']) ? $match['multi'] : null;
			$property = isset($match['property']) ? $match['property'] : null;
			if (strpos($match['reference'], '*')) {
				return '$this->objects->random(' . var_export($match['reference'], true) . ', ' . var_export($multi, true) . ', ' . var_export($property, true) . ')';
			}
			if (null !== $multi) {
				throw new \UnexpectedValueException('To use multiple references you must use a mask like "'.$match['multi'].'x @user*", otherwise you would always get only one item.');
			}
			return '$this->objects->find(' . var_export($match['reference'], true) . ', ' . var_export($property, true) . ')';
		}, $args);

		$locale = var_export($matches['locale'], true);
		$name = var_export($matches['name'], true);

		return eval('return $this->fake(' . $name . ', ' . $locale . ', ' . $args . ');');
	}

	private function fake($formatter, $locale = null)
	{
		$args = array_slice(func_get_args(), 2);

		if ($formatter == 'current') {
			if ($this->currentValue === null) {
				throw new \UnexpectedValueException('Cannot use <current()> out of fixtures ranges or enum');
			}

			return $this->currentValue;
		}

		return $this->getGenerator($locale)->format($formatter, $args);
	}

	/**
	 * Get the generator for this locale
	 *
	 * @param string $locale the requested locale, defaults to constructor injected default
	 *
	 * @return \Faker\Generator the generator for the requested locale
	 */
	private function getGenerator($locale = null)
	{
		$locale = $locale ?: $this->defaultLocale;

		if (!isset($this->generators[$locale])) {
			$generator = \Faker\Factory::create($locale);
			foreach ($this->providers as $provider) {
				$generator->addProvider($provider);
			}
			$this->generators[$locale] = $generator;
		}

		return $this->generators[$locale];
	}

}