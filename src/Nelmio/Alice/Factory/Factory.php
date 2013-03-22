<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Factory;

/**
 * Factory provides fluent interfaces for defining and building datasets.
 *
 * @author Fabian Spillner <fabian.spillner@gmail.com>
 */
class Factory
{
    protected $definitions;

    public function __construct()
    {
        $this->definitions = new Definitions;
    }

    public function define($name)
    {
        return $this->definitions->add(new Definition($this, $this->definitions, $name));
    }

    public function build($name, $num = 1, $values = array())
    {
        if (!$this->definitions->exists($name)) {
            throw new \Exception("Unknown definition name");
        }

        return $this->definitions->get($name)->toArray($num, $values);
    }
}

class Definitions
{
    protected $list = array();

    public function add(Definition $def)
    {
        $this->list[$def->getName()] = $def;

        return $def;
    }

    public function get($name)
    {
        return $this->list[$name];
    }

    public function exists($name)
    {
        return isset($this->list[$name]);
    }
}

class Definition
{
    protected $factory;

    protected $definitions;

    protected $name;

    protected $parent = false;

    protected $className;

    protected $values = array();

    protected $assocations = array();

    private $inherited = false;

    public function __construct(Factory $factory, Definitions $definitions, $name)
    {
        $this->factory = $factory;
        $this->definitions = $definitions;
        $this->name = $name;

        if (count($names = explode("<", $name)) > 1) {
            $this->name = trim($names[0]);
            $this->parent = trim($names[1]);
        }
    }

    public function of($className)
    {
        $this->className = $className;

        return $this;
    }

    public function parent($parent)
    {
        $this->parent = $parent;
    }

    public function constructWith()
    {
        return $this->values(array("__construct" => func_get_args()));
    }

    public function values(array $values)
    {
        $this->values = array_merge($this->values, $values);

        return $this;
    }

    public function unique($name, $value)
    {
        $this->values["$name (unique)"] = $value;

        return $this;
    }

    public function assocOne($rel, $name, $values = array())
    {
        $this->values(array($rel => "@$name"));

        return $this->assocation($name, 1, $values);
    }

    public function assocMany($rel, $name, $num = 1, $values = array())
    {
        if (is_array($name)) {
            $names = $name;

            foreach ($names as $name) {
                $this->assocation($name, 1, $values);
            }

            $values = array_map(function($n) { return "@" . $n; }, $names);
            $values = '[' . implode(', ', $values) . ']';

            return $this->values(array($rel => $values));
        }

        $this->values(array($rel => "${num}x @$name"));

        return $this->assocation($name, $num, $values);
    }

    protected function assocation($name, $num = 1, $values = array())
    {
        $matches = array();
        preg_match("/([a-zA-Z0-9_-]*)/", $name, $matches);
        $name = $matches[1];

        $this->assocations[$name] = array('num' => $num, 'values' => $values);

        return $this;
    }

    public function toArray($num = 1, $overrideValues = array())
    {
        $this->inheritFromParent();

        $dataset = array();

        foreach ($this->assocations as $assocName => $assoc) {
            $data = $this->factory->build($assocName, $assoc['num'], $assoc['values']);

            $assocClass = array_keys($data)[0];

            if (isset($dataset[$assocClass])) {
                $dataset[$assocClass] = array_merge($dataset[$assocClass], $data[$assocClass]);
            } else {
                $dataset = array_merge($dataset, $data);
            }
        }

        $key = $num == 1 ? $this->name : $this->name . "{1..$num}";

        $data = array(
            $key => array_merge($this->values, $overrideValues)
        );

        if (isset($dataset[$this->className])) {
            $dataset[$this->className] = array_merge($dataset[$this->className], $data);
        } else {
            $dataset = array_merge($dataset, array($this->className => $data));
        }

        return $dataset;
    }

    public function getName()
    {
        return $this->name;
    }

    public function end()
    {
        return $this->factory;
    }

    protected function getValues()
    {
        return $this->values;
    }

    protected function getAssocations()
    {
        return $this->assocations;
    }

    protected function getClassName()
    {
        return $this->className;
    }

    protected function inheritFromParent()
    {
        if ($this->parent && !$this->inherited) {
            $parentDefinition = $this->definitions->get($this->parent);
            $parentDefinition->inheritFromParent();

            $this->className = empty($this->className) ? $parentDefinition->getClassName() : $this->className;
            $this->values = array_merge($parentDefinition->getValues(), $this->values);
            $this->assocations = array_merge($parentDefinition->getAssocations(), $this->assocations);

            $this->inherited = true;
        }
    }
}

