<?php

namespace Nelmio\Alice\Fixtures;

use Nelmio\Alice\Instances\Processor\Processable;
use Nelmio\Alice\Util\FlagParser;

class PropertyDefinition extends Processable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $nameFlags;

    public function __construct($name, $value)
    {
        list($this->name, $this->nameFlags) = FlagParser::parse($name);
        parent::__construct($value);
    }

    /**
     * @return string
     **/
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getNameFlags()
    {
        return $this->nameFlags;
    }

    /**
     * returns true if this property requires unique values
     *
     * @return boolean
     **/
    public function requiresUnique()
    {
        return isset($this->nameFlags['unique']);
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
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }
}
