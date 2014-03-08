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

use Nelmio\Alice\Instances\Collection;

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
	}

	public function setProviders(array $providers)
	{
		$this->providers = $providers;
		$this->emptyGenerators();
	}

	public function setCurrentValue($value)
	{
		$this->currentValue = $value;
	}

	public function unsetCurrentValue()
	{
		$this->currentValue = null;
	}

	public function process($data, array $variables)
	{
		if (is_array($data)) {
			foreach ($data as $key => $val) {
				$data[$key] = $this->process($val, $variables);
			}

			return $data;
		}

		// check for conditional values (20%? true : false)
		if (is_string($data) && preg_match('{^(?<threshold>[0-9.]+%?)\? (?<true>.+?)(?: : (?<false>.+?))?$}', $data, $match)) {
			// process true val since it's always needed
			$trueVal = $this->process($match['true'], $variables);

			// compute threshold and check if we are beyond it
			$threshold = $match['threshold'];
			if (substr($threshold, -1) === '%') {
				$threshold = substr($threshold, 0, -1) / 100;
			}
			$randVal = mt_rand(0, 100) / 100;
			if ($threshold > 0 && $randVal <= $threshold) {
				return $trueVal;
			} else {
				$emptyVal = is_array($trueVal) ? array() : null;

				if (isset($match['false']) && '' !== $match['false']) {
					return $this->process($match['false'], $variables);
				}

				return $emptyVal;
			}
		}

		// return non-string values
		if (!is_string($data)) {
			return $data;
		}

		$that = $this;
		// replaces a placeholder by the result of a ->fake call
		$replacePlaceholder = function ($matches) use ($variables, $that) {
			$args = isset($matches['args']) && '' !== $matches['args'] ? $matches['args'] : null;

			if (!$args) {
				return $that->fake($matches['name'], $matches['locale']);
			}

			// replace references to other variables in the same object
			$args = preg_replace_callback('{\{?\$([a-z0-9_]+)\}?}i', function ($match) use ($variables) {
				if (array_key_exists($match[1], $variables)) {
					return '$variables['.var_export($match[1], true).']';
				}

				return $match[0];
			}, $args);

			// replace references to other objects
			$args = preg_replace_callback('{(?:\b|^)(?:(?<multi>\d+)x )?(?<!\\\\)@(?<reference>[a-z0-9_.*-]+)(?:\->(?<property>[a-z0-9_-]+))?(?:\b|$)}i', function ($match) use ($that) {
				$multi    = ('' !== $match['multi']) ? $match['multi'] : null;
				$property = isset($match['property']) ? $match['property'] : null;
				if (strpos($match['reference'], '*')) {
					return '$that->objects->random(' . var_export($match['reference'], true) . ', ' . var_export($multi, true) . ', ' . var_export($property, true) . ')';
				}
				if (null !== $multi) {
					throw new \UnexpectedValueException('To use multiple references you must use a mask like "'.$match['multi'].'x @user*", otherwise you would always get only one item.');
				}
				return '$that->objects->find(' . var_export($match['reference'], true) . ', ' . var_export($property, true) . ')';
			}, $args);

			$locale = var_export($matches['locale'], true);
			$name = var_export($matches['name'], true);

			return eval('return $that->fake(' . $name . ', ' . $locale . ', ' . $args . ');');
		};

		// format placeholders without preg_replace if there is only one to avoid __toString() being called
		$placeHolderRegex = '<(?:(?<locale>[a-z]+(?:_[a-z]+)?):)?(?<name>[a-z0-9_]+?)\((?<args>(?:[^)]*|\)(?!>))*)\)>';
		if (preg_match('#^'.$placeHolderRegex.'$#i', $data, $matches)) {
			$data = $replacePlaceholder($matches);
		} else {
			// format placeholders inline
			$data = preg_replace_callback('#'.$placeHolderRegex.'#i', function ($matches) use ($replacePlaceholder) {
				return $replacePlaceholder($matches);
			}, $data);
		}

		// process references
		if (is_string($data) && preg_match('{^(?:(?<multi>\d+)x )?@(?<reference>[a-z0-9_.*-]+)(?:\->(?<property>[a-z0-9_-]+))?$}i', $data, $matches)) {
			$multi    = ('' !== $matches['multi']) ? $matches['multi'] : null;
			$property = isset($matches['property']) ? $matches['property'] : null;
			if (strpos($matches['reference'], '*')) {
				$data = $this->objects->random($matches['reference'], $multi, $property);
			} else {
				if (null !== $multi) {
					throw new \UnexpectedValueException('To use multiple references you must use a mask like "'.$matches['multi'].'x @user*", otherwise you would always get only one item.');
				}
				$data = $this->objects->find($matches['reference'], $property);
			}
		}

		// unescape at-signs
		if (is_string($data) && false !== strpos($data, '\\')) {
			$data = preg_replace('{\\\\([@\\\\])}', '$1', $data);
		}

		return $data;
	}

	private function fake($formatter, $locale = null, $arg = null, $arg2 = null, $arg3 = null)
	{
		$args = func_get_args();
		array_shift($args);
		array_shift($args);

		if ($formatter == 'current') {
			if ($this->currentValue === null) {
				throw new \UnexpectedValueException('Cannot use <current()> out of fixtures ranges or enum');
			}

			return $this->currentValue;
		}

		return $this->getGenerator($locale)->format($formatter, $args);
	}

	private function emptyGenerators()
	{
		$this->generators = array();
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