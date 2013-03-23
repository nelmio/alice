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

use Symfony\Component\Yaml\Yaml as YamlParser;

/**
 * Factory provides fluent interfaces for defining and building datasets.
 *
 * @author Fabian Spillner <fabian.spillner@gmail.com>
 */
class Factory
{
    protected $definitions;

    protected $uses = array();

    public function __construct()
    {
        $this->definitions = new Definitions;
    }

    public function define($name, $override = false)
    {
        if (false === $override || !$this->definitions->exists($name)) {
            return $this->definitions->add(new Definition($this, $this->definitions, $name));

        }

        return $this->definitions->get($name);
    }

    public function import($filename)
    {
        if (preg_match("/.*\.yml/", $filename)) {
            ob_start();
            $includeWrapper = function () use ($filename) {
                return include $filename;
            };
            $data = $includeWrapper();
            if (true !== $data) {
                $yaml = ob_get_clean();
                $data = YamlParser::parse($yaml);
            }
        } else {
            $includeWrapper = function () use ($filename) {
                ob_start();
                $res = include $filename;
                ob_end_clean();

                return $res;
            };
            $data = $includeWrapper();
        }

        if (!is_array($data)) {
            throw new \UnexpectedValueException('Import data must be an array of data');
        }

        foreach ($data as $className => $dataset) {
            foreach ($dataset as $name => $data) {
                # todo automatic detection of assocations?
                $this
                    ->define($name)
                    ->of($className)
                    ->values($data)
                ;
            }
        }

        return $this;
    }

    public function with($name, $sum = 1, $values = array())
    {
        $this->uses[$name] = array($sum, $values);

        return $this;
    }

    public function build($name, $num = 1, $values = array())
    {
        if (!$this->definitions->exists($name)) {
            throw new \Exception("Unknown definition name");
        }

        # $this->definitions->get($name)->toArray($num, $values);
        $definition = $this->definitions->get($name);

        foreach ($this->uses as $useName => $useDataset) {
            list($useNum, $useValues) = $useDataset;
            $definition->with($useName, $useNum, $useValues);
        }

        $this->uses = array();

        return $definition->toArray($num, $values);
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

    protected $uses = array();

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

        return $this->with($name, $num, $values);
    }

    public function with($name, $num = 1, $values = array())
    {
        $this->uses[$name] = array('num' => $num, 'values' => $values);

        return $this;
    }

    public function toArray($num = 1, $overrideValues = array())
    {
        $this->inheritFromParent();

        $dataset = array();

        foreach ($this->uses as $useName => $useData) {
            $data = $this->factory->build($useName, $useData['num'], $useData['values']);

            $useClass = array_keys($data);
            $useClass = $useClass[0];

            if (isset($dataset[$useClass])) {
                $dataset[$useClass] = array_merge($dataset[$useClass], $data[$useClass]);
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

    protected function getClassName()
    {
        return $this->className;
    }

    protected function getUses()
    {
        return $this->uses;
    }

    protected function inheritFromParent()
    {
        if ($this->parent && !$this->inherited) {
            $parentDefinition = $this->definitions->get($this->parent);
            $parentDefinition->inheritFromParent();

            $this->className = empty($this->className) ? $parentDefinition->getClassName() : $this->className;
            $this->values = array_merge($parentDefinition->getValues(), $this->values);
            $this->uses = array_merge($parentDefinition->getUses(), $this->uses);

            $this->inherited = true;
        }
    }
}

