<?php

namespace Nelmio\Alice\Instances;

use Nelmio\Alice\Util\FlagParser;

class PropertyDefinition {
	
	/**
	 * @var string
	 */
	private $name;
	
	/**
	 * @var string
	 */
	private $value;

	/**
	 * @var array
	 */
	private $nameFlags;

	/**
	 * @var array
	 */
	private $matches = array();

	function __construct($name, $value) {
		list($this->name, $this->nameFlags) = FlagParser::parse($name);
		$this->value = $value;
	}

	/**
	 * @return string
	 **/
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 **/
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return array
	 */
	public function getNameFlags()
	{
		return $this->nameFlags;
	}

	/**
	 * returns true if this definition is for a property to be set on the instance
	 *
	 * @return boolean
	 */
	public function isBasic()
	{
		return !$this->isConstructor() && !$this->isCustomSetter();
	}

	/**
	 * returns true if this definition is the constructor
	 *
	 * @return boolean
	 */
	public function isConstructor()
	{
		return $this->name == '__construct';
	}

	/**
	 * returns true if this definition is the custom setter
	 *
	 * @return boolean
	 */
	public function isCustomSetter()
	{
		return $this->name == '__set';
	}

	/**
	 * tests whether this property's value matches the regex, and appends new matches to the matches array
	 *
	 * @param string $regexString
	 * @return boolean
	 */
	public function valueMatches($regexString)
	{
		if (preg_match('{^(?<threshold>[0-9.]+%?)\? (?<true>.+?)(?: : (?<false>.+?))?$}', $this->value, $matches)) {
			$this->matches = array_merge($this->matches, $matches);
			return true;
		}
		return false;
	}

	/**
	 * allows us to access the list of matches from outside the property class
	 */
	public function __get($property)
	{
		return $this->matches[$property];
	}

	public function __toString()
	{
		return $this->getValue();
	}

}