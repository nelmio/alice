<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures;

use Nelmio\Alice\Util\FlagParser;

class Fixture
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $spec;

    /**
     * @var array
     */
    protected $properties;

    /**
     * @var array
     */
    protected $classFlags;

    /**
     * @var array
     */
    protected $nameFlags;

    /**
     * @var string
     */
    protected $valueForCurrent;

    /**
     * @var array
     */
    protected $setProperties = [];

    /**
     * built a class representation of a fixture
     *
     * @param string $class
     * @param string $name
     * @param array  $spec
     * @param string $valueForCurrent - when <current()> is called, this value is used
     */
    public function __construct($class, $name, array $spec, $valueForCurrent)
    {
        list($this->class, $this->classFlags) = FlagParser::parse($class);
        list($this->name, $this->nameFlags)   = FlagParser::parse($name);

        $this->spec            = $spec;
        $this->valueForCurrent = $valueForCurrent;

        $this->properties = [];
        foreach ($spec as $propertyName => $propertyValue) {
            $this->addProperty($propertyName, $propertyValue);
        }
    }

    /**
     * returns true when the fixture has either the local class or name flag
     *
     * @return boolean
     */
    public function isLocal()
    {
        return $this->hasClassFlag('local') || $this->hasNameFlag('local');
    }

    /**
     * returns true when the fixture has been flagged as a template
     */
    public function isTemplate()
    {
        return $this->hasNameFlag('template');
    }

    /**
     * extends this fixture by the given template
     *
     * @param Fixture $template
     */
    public function extendTemplate(Fixture $template)
    {
        if (!$template->isTemplate()) {
            throw new \InvalidArgumentException('Argument must be a template, not just a fixture.');
        }

        foreach ($template->properties as $property) {
            if (!isset($this->spec[$property->getName()])) {
                $this->addProperty($property->getName(), $property->getValue());
            }
        }
    }

    /**
     * returns a list of templates to extend
     *
     * @return array
     */
    public function getExtensions()
    {
        $extensions = array_filter(
            array_keys($this->nameFlags),
            function ($flag) {
                return 1 === preg_match('#^extends\s*(.+)$#', $flag);
            }
        );

        return array_map(
            function ($extension) {
                return str_replace('extends ', '', $extension);
            },
            $extensions
        );
    }

    /**
     * returns true if the fixture has extensions
     *
     * @return boolean
     */
    public function hasExtensions()
    {
        return count($this->getExtensions()) > 0;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * returns the list of properties with the complex properties (__construct, __set, etc) filtered out
     *
     * @return array
     */
    public function getProperties()
    {
        return array_filter($this->properties, function ($property) { return $property->isBasic(); });
    }

    /**
     * get the list of class flags on this fixture
     *
     * @return array
     */
    public function getClassFlags()
    {
        return $this->classFlags;
    }

    /**
     * returns true if this fixture has the given class flag
     *
     * @return boolean
     */
    public function hasClassFlag($flag)
    {
        return in_array($flag, array_keys($this->classFlags));
    }

    /**
     * get the list of name flags on this fixture
     *
     * @return array
     */
    public function getNameFlags()
    {
        return $this->nameFlags;
    }

    /**
     * returns true if this fixture has the given name flag
     *
     * @return boolean
     */
    public function hasNameFlag($flag)
    {
        return in_array($flag, array_keys($this->nameFlags));
    }

    /**
     * @return string
     */
    public function getValueForCurrent()
    {
        return $this->valueForCurrent;
    }

    /**
     * returns the name of the static method to use as the constructor
     *
     * @return string
     */
    public function getConstructorMethod()
    {
        $constructorComponents = $this->getConstructorComponents();

        return $constructorComponents['method'];
    }

    /**
     * returns the list of arguments to pass to the constructor
     *
     * @return array
     */
    public function getConstructorArgs()
    {
        $constructorComponents = $this->getConstructorComponents();

        return $constructorComponents['args'];
    }

    /**
     * returns true when the __construct property has been specified in the spec
     *
     * @return boolean
     */
    public function shouldUseConstructor()
    {
        return is_null($this->getConstructor()) || $this->getConstructor()->getValue();
    }

    /**
     * returns true when the __set property has been specified in the spec
     *
     * @return boolean
     */
    public function hasCustomSetter()
    {
        return !is_null($this->getCustomSetter());
    }

    /**
     * returns the name of the method to use as the custom setter
     *
     * @return string
     */
    public function getCustomSetter()
    {
        return isset($this->properties['__set']) ? $this->properties['__set'] : null;
    }

    /**
     * allows registering a set property value on the fixture itself
     *
     * @param string $property
     * @param mixed  $value
     */
    public function setPropertyValue($property, $value)
    {
        $this->setProperties[$property] = $value;
    }

    /**
     * returns the value of a property that has been registered as set
     *
     * @return mixed $value
     */
    public function getPropertyValue($property)
    {
        return $this->setProperties[$property];
    }

    /**
     * get a list of properties that have been registered as set
     *
     * @return array
     */
    public function getSetProperties()
    {
        return $this->setProperties;
    }

    /**
     * display the fixture as a string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * creates and adds a PropertyDefinition to the fixture with the given name and value
     *
     * @param string $name
     * @param mixed  $value
     */
    protected function addProperty($name, $value)
    {
        return $this->properties[$name] = new PropertyDefinition($name, $value);
    }

    /**
     * returns the constructor property
     *
     * @return PropertyDefinition
     */
    protected function getConstructor()
    {
        return isset($this->properties['__construct']) ? $this->properties['__construct'] : null;
    }

    //
    // Sequential arrays call the constructor, hashes call a static method
    //
    // array('foo', 'bar') => new $fixture->getClass()('foo', 'bar')
    // array('foo' => array('bar')) => $fixture->getClass()::foo('bar')
    //
    protected function getConstructorComponents()
    {
        if (is_null($this->getConstructor())) {
            return ['method' => '__construct', 'args' => []];
        }

        $constructorValue = $this->getConstructor()->getValue();
        if (!is_array($constructorValue)) {
            throw new \UnexpectedValueException("The __construct call in object '{$this}' must be defined as an array of arguments or false to bypass it");
        }

        list($method, $args) = each($constructorValue);
        if ($method !== 0) {
            if (!is_callable([$this->class, $method])) {
                throw new \UnexpectedValueException("Cannot call static method '{$method}' on class '{$this->class}' as a constructor for object '{$this}'");
            }
            if (!is_array($args)) {
                throw new \UnexpectedValueException("The static '{$method}' call in object '{$this}' must be given an array");
            }

            return ['method' => $method, 'args' => $args];
        }

        return ['method' => '__construct', 'args' => $constructorValue];
    }
}
