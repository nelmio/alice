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

use Nelmio\Alice\Instances\Processor\ProcessableInterface;

class Processable implements ProcessableInterface {

	/**
	 * @var string
	 */
	protected $value;

	/**
	 * @var array
	 */
	public $matches = array();

	function __construct($value) {
		$this->value = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function valueMatches($regexString)
	{
		if (preg_match($regexString, $this->value, $matches)) {
			$this->matches = array_merge($this->matches, $matches);
			return true;
		}
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMatch($name)
	{
		return $this->matches[$name];
	}

}