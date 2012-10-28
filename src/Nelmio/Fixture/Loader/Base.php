<?php

/*
 * This file is part of the Nelmio Fixture package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Fixture\Loader;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml as YamlParser;
use Nelmio\Fixture\LoaderInterface;
use Nelmio\Fixture\ORMInterface;

/**
 * Loads fixtures from an array or php file
 *
 * The php code if $data is a file has access to $loader->fake() to
 * generate data and must return an array of the format below.
 *
 * The array format must follow this example:
 *
 *     array(
 *         'Namespace\Class' => array(
 *             'name' => array(
 *                 'property' => 'value',
 *                 'property2' => 'value',
 *             ),
 *             'name2' => array(
 *                 [...]
 *             ),
 *         ),
 *     )
 */
class Base implements LoaderInterface
{
    protected $references = array();

    public function __construct($locale = 'en_US', array $providers = array())
    {
        $this->generator = \Faker\Factory::create($locale);
        foreach ($providers as $provider) {
            $this->generator->addProvider($provider);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function load($data, ORMInterface $persister)
    {
        if (!is_array($data)) {
            $loader = $this;
            $includeWrapper = function () use ($data, $loader) {
                return include $data;
            };
            $data = $includeWrapper();
            if (!is_array($data)) {
                throw new \UnexpectedValueException('Included PHP files must return an array of data');
            }
        }

        $objects = array();

        foreach ($data as $class => $instances) {
            foreach ($instances as $name => $spec) {
                if (preg_match('#\{([0-9]+)\.\.(\.?)([0-9]+)\}#i', $name, $match)) {
                    $from = $match[1];
                    $to = empty($match[2]) ? $match[3] : $match[3] - 1;
                    if ($from > $to) {
                        list($to, $from) = array($from, $to);
                    }
                    for ($i = $from; $i <= $to; $i++) {
                        $objects[] = $this->createObject($class, str_replace($match[0], $i, $name), $spec);
                    }
                } else {
                    $objects[] = $this->createObject($class, $name, $spec);
                }
            }
        }

        $persister->persist($objects);

        return $objects;
    }

    /**
     * {@inheritDoc}
     */
    public function getReference($name)
    {
        if (isset($this->references[$name])) {
            return $this->references[$name];
        }

        throw new \UnexpectedValueException('Reference '.$name.' is not defined');
    }

    /**
     * {@inheritDoc}
     */
    public function getReferences()
    {
        return $this->references;
    }

    public function fake($formatter, $arg = null, $arg2 = null, $arg3 = null)
    {
        $args = func_get_args();
        array_shift($args);

        return $this->generator->format($formatter, $args);
    }

    private function createObject($class, $name, $data)
    {
        $obj = new $class;
        $variables = array();
        foreach ($data as $key => $val) {
            if (is_array($val) && '{' === key($val)) {
                throw new \RuntimeException('Misformatted string in object '.$name.', '.$key.'\'s value should be quoted if you used yaml.');
            }

            // process values
            $val = $this->process($val, $variables);

            // add relations if available
            if (is_array($val)
                && (method_exists($obj, $method = 'add'.rtrim($key, 's').'s')
                    || method_exists($obj, $method = 'add'.rtrim($key, 's'))
                )
            ) {
                foreach ($val as $rel) {
                    $obj->{$method}($rel);
                }
            } elseif (method_exists($obj, 'set'.$key)) {
                // set value
                $obj->{'set'.$key}($val);
                $variables[$key] = $val;
            } elseif (property_exists($obj, $key)) {
                $obj->$key = $val;
                $variables[$key] = $val;
            } else {
                throw new \UnexpectedValueException('Could not determine how to assign '.$key.' to a '.$class.' object.');
            }
        }

        return $this->references[$name] = $obj;
    }

    private function process($data, array $variables)
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
            $randVal = rand(0, 100) / 100;
            if ($threshold > 0 && $randVal <= $threshold) {
                return $trueVal;
            } else {
                $emptyVal = is_array($trueVal) ? array() : null;

                return isset($match['false']) ? $this->process($match['false'], $variables) : $emptyVal;
            }
        }

        // return non-string values
        if (!is_string($data)) {
            return $data;
        }

        $that = $this;
        // replaces a placeholder by the result of a ->fake call
        $replacePlaceholder = function ($matches) use ($variables, $that) {
            $args = (!empty($matches['args']) ? ', '.$matches['args'] : '');

            if (!$args) {
                return $that->fake($matches['name']);
            }

            // replace references to other variables in the same object
            $args = preg_replace_callback('{\{?\$([a-z0-9_]+)\}?}i', function ($match) use ($variables) {
                if (isset($variables[$match[1]])) {
                    return '$variables['.var_export($match[1], true).']';
                }
                return $match[0];
            }, $args);

            return eval('return $that->fake('.var_export($matches['name'], true) . $args.');');
        };

        // format placeholders without preg_replace if there is only one to avoid __toString() being called
        if (preg_match('#^<(?<name>[a-z0-9_]+?)(?:\((?<args>.+?)\))?>$#i', $data, $matches)) {
            $data = $replacePlaceholder($matches);
        } else {
            // format placeholders inline
            $data = preg_replace_callback('#<(?<name>[a-z0-9_]+?)(?:\((?<args>.+?)\))?>#i', function ($matches) use ($replacePlaceholder) {
                return $replacePlaceholder($matches);
            }, $data);
        }

        // process references
        if (is_string($data) && preg_match('{^(?:(?<multi>\d+)x )?@(?<reference>[a-z0-9_.*-]+)$}i', $data, $matches)) {
            if (strpos($matches['reference'], '*')) {
                $data = $this->getRandomReferences($matches['reference'], isset($matches['multi']) ? $matches['multi'] : 1);
            } else {
                $data = $this->getReference($matches['reference']);
            }
        }

        return $data;
    }

    private function getRandomReferences($mask, $count = 1)
    {
        if ($count === 0) {
            return array();
        }

        $availableRefs = array();
        foreach ($this->references as $key => $val) {
            if (preg_match('{^'.str_replace('*', '.+', $mask).'$}', $key)) {
                $availableRefs[$key] = $val;
            }
        }

        if (!$availableRefs) {
            throw new \UnexpectedValueException('Reference mask "'.$mask.'" did not match any existing reference, make sure the object is created after its references');
        }

        shuffle($availableRefs);

        return array_slice($availableRefs, 0, min($count, count($availableRefs)));
    }
}
