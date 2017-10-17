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
     * @var PropertyDefinition[]
     */
    protected $properties;

    /**
     * @var array e.g. ['template' => true, 'extends dummy' => true]
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
     * @param string      $class
     * @param string      $name
     * @param array       $spec
     * @param string|null $valueForCurrent When <current()> is called, this value is used
     */
    public function __construct($class, $name, array $spec, $valueForCurrent)
    {
        list($this->class, $this->classFlags) = FlagParser::parse($class);
        list($this->name, $this->nameFlags) = FlagParser::parse($name);

        $this->checkName($name);

        $this->spec = $spec;
        $this->valueForCurrent = $valueForCurrent;

        $this->properties = [];
        foreach ($spec as $propertyName => $propertyValue) {
            $this->addPropertyDefinition(
                new PropertyDefinition($propertyName, $propertyValue)
            );
        }
    }

    /**
     * @return boolean true when the fixture has either the local class or name flag.
     */
    public function isLocal()
    {
        $isLocal = $this->hasClassFlag('local') || $this->hasNameFlag('local');
        if ($isLocal) {
            @trigger_error(
                'The local flag is deprecated since 2.3.0 and will no longer be supported in 3.0. See '
                .'https://github.com/nelmio/alice/issues/514 for more details.',
                E_USER_DEPRECATED
            );
        }

        return $isLocal;
    }

    /**
     * @return boolean true when the fixture has been flagged as a template.
     */
    public function isTemplate()
    {
        return $this->hasNameFlag('template');
    }

    /**
     * Extends this fixture by the given template.
     *
     * @param Fixture $template
     */
    public function extendTemplate(Fixture $template)
    {
        if (!$template->isTemplate()) {
            throw new \InvalidArgumentException('Argument must be a template, not just a fixture.');
        }

        foreach ($template->properties as $property) {
            if (!$this->hasProperty($property->getName())) {
                $this->addPropertyDefinition($property);
            }
        }
    }

    /**
     * @return string[] list of templates (references) to extend.
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
     * @return boolean true if the fixture has extensions.
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
     * @param string $name
     *
     * @return bool
     */
    public function hasProperty($name)
    {
        foreach ($this->properties as $property) {
            if ($property->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return PropertyDefinition[] list of properties with the complex properties (__construct, __set, etc) filtered
     *                              out.
     */
    public function getProperties()
    {
        return array_filter(
            $this->properties,
            function (PropertyDefinition $property) {
                return $property->isBasic();
            }
        );
    }

    /**
     * @return array The list of class flags on this fixture.
     */
    public function getClassFlags()
    {
        return $this->classFlags;
    }

    /**
     * @param string $flag
     *
     * @return bool true if this fixture has the given class flag
     */
    public function hasClassFlag($flag)
    {
        return in_array($flag, array_keys($this->classFlags));
    }

    /**
     * @return array List of name flags on this fixture
     */
    public function getNameFlags()
    {
        return $this->nameFlags;
    }

    /**
     * @param string $flag
     *
     * @return bool true if this fixture has the given name flag.
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
     * @return string Name of the static method to use as the constructor.
     */
    public function getConstructorMethod()
    {
        $constructorComponents = $this->getConstructorComponents();

        return $constructorComponents['method'];
    }

    /**
     * @return array List of arguments to pass to the constructor
     */
    public function getConstructorArgs()
    {
        $constructorComponents = $this->getConstructorComponents();

        return $constructorComponents['args'];
    }

    /**
     * @return bool true when the __construct property has been specified in the spec.
     */
    public function shouldUseConstructor()
    {
        return is_null($this->getConstructor()) || $this->getConstructor()->getValue();
    }

    /**
     * @return bool true when the __set property has been specified in the spec.
     */
    public function hasCustomSetter()
    {
        return !is_null($this->getCustomSetter());
    }

    /**
     * @return PropertyDefinition Name of the method to use as the custom setter.
     */
    public function getCustomSetter()
    {
        return isset($this->properties['__set']) ? $this->properties['__set'] : null;
    }

    /**
     * Allows registering a set property value on the fixture itself.
     *
     * @param string $property
     * @param mixed  $value
     */
    public function setPropertyValue($property, $value)
    {
        $this->setProperties[$property] = $value;
    }

    /**
     * @param  string $property
     *
     * @return mixed The value of a property that has been registered as set
     */
    public function getPropertyValue($property)
    {
        return $this->setProperties[$property];
    }

    /**
     * @return array List of properties that have been registered as set
     */
    public function getSetProperties()
    {
        return $this->setProperties;
    }

    /**
     * @return string Displays the fixture as a string.
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Creates and adds a PropertyDefinition to the fixture with the given name and value.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return PropertyDefinition
     *
     * @deprecated Has been deprecated since 2.2.0. Use ::addPropertyDefinition() instead.
     */
    protected function addProperty($name, $value)
    {
        $property = new PropertyDefinition($name, $value);

        return $this->properties[$property->getName()] = $property;
    }

    private function addPropertyDefinition(PropertyDefinition $property)
    {
        $this->properties[$property->getName()] = $property;
    }

    /**
     * @return PropertyDefinition The constructor property
     */
    protected function getConstructor()
    {
        return isset($this->properties['__construct']) ? $this->properties['__construct'] : null;
    }

    /**
     * Sequential arrays call the constructor, hashes call a static method
     *
     * array('foo', 'bar') => new $fixture->getClass()('foo', 'bar')
     * array('foo' => array('bar')) => $fixture->getClass()::foo('bar')
     */
    protected function getConstructorComponents()
    {
        if (is_null($this->getConstructor())) {
            return ['method' => '__construct', 'args' => []];
        }

        $constructorValue = $this->getConstructor()->getValue();
        if (!is_array($constructorValue)) {
            throw new \UnexpectedValueException("The __construct call in object '{$this}' must be defined as an array of arguments or false to bypass it");
        }

        foreach ($constructorValue as $method => $args) {
            if ($method !== 0) {
                if (!is_callable([$this->class, $method])) {
                    throw new \UnexpectedValueException(
                        "Cannot call static method '{$method}' on class '{$this->class}' as a constructor for object '{$this}'"
                    );
                }

                if (!is_array($args)) {
                    throw new \UnexpectedValueException(
                        "The static '{$method}' call in object '{$this}' must be given an array"
                    );
                }

                return ['method' => $method, 'args' => $args];
            }

            return ['method' => '__construct', 'args' => $constructorValue];
        }
    }

    /**
     * @param string $name
     */
    private function checkName($name)
    {
        if (1 === strlen($name) && 1 !== preg_match('/\p{L}/', $name)) {
            @trigger_error(
                sprintf(
                    'Fixture references 1 character long should be composed of a letter. Found "%s" instead. This is '
                    .'is deprecated since 2.2.0 and will be removed in Alice 3.0',
                    $name
                ),
                E_USER_DEPRECATED
            );
        } elseif (1 !== preg_match('/[\p{L}\d\._\/]+/', $name)) {
            @trigger_error(
                sprintf(
                    'Fixture references should only be composed of letters, digits, periods ("."), underscores ("_") '
                    .' and slashes ("/"). The usage of other characters is deprecated since 2.2.0 and will no longer be'
                    .'supported in Alice 3.0',
                    $name
                ),
                E_USER_DEPRECATED
            );
        }
    }
}
