<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;

use Symfony\Component\Form\Util\FormUtil;
use Symfony\Component\PropertyAccess\StringUtil;
use Psr\Log\LoggerInterface;
use Nelmio\Alice\ORMInterface;
use Nelmio\Alice\LoaderInterface;
use Nelmio\Alice\Instances\Builders;
use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\Instances\Processor;
use Nelmio\Alice\Util\FlagParser;
use Nelmio\Alice\Util\TypeHintChecker;

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
    /**
     * @var Collection
     */
    protected $instances;

    /**
     * @var ORMInterface
     */
    protected $manager;

    /**
     * @var array
     */
    private $uniqueValues = array();

    /**
     * @var callable|LoggerInterface
     */
    private $logger;

    /**
     * @param string $locale default locale to use with faker if none is
     *      specified in the expression
     * @param array $providers custom faker providers in addition to the default
     *      ones from faker
     * @param int $seed a seed to make sure faker generates data consistently across
     *      runs, set to null to disable
     */
    public function __construct($locale = 'en_US', array $providers = array(), $seed = 1)
    {
        $this->instances       = new Collection;
        $this->typeHintChecker = new TypeHintChecker;
        $this->processor       = new Processor($locale, $this->instances, $providers);

        $this->instanceBuilders = array(
            new Builders\RangeBuilder($this->instances, $this->processor, $this->typeHintChecker),
            new Builders\ListBuilder($this->instances, $this->processor, $this->typeHintChecker),
            new Builders\BaseBuilder($this->instances, $this->processor, $this->typeHintChecker)
        );

        if (is_numeric($seed)) {
            mt_srand($seed);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function load($dataOrFilename)
    {
        // ensure our data is loaded
        $data = !is_array($dataOrFilename) ? $this->parseFile($dataOrFilename) : $dataOrFilename;

        // create instances
        $instances = $this->buildInstances($data);

        // populate instances
        $objects = array();
        foreach ($instances as $instance) {
            $this->processor->setCurrentValue($instance->currentValue);
            $this->populateObject($instance->object, $instance->class, $instance->name, $instance->spec);
            $this->processor->unsetCurrentValue();

            // add the object in the object store unless it's local
            if (!isset($instance->classFlags['local']) && !isset($instance->instanceFlags['local'])) {
                $objects[$instance->name] = $instance->object;
            }
        }

        return $objects;
    }

    /**
     * {@inheritDoc}
     */
    public function getReference($name, $property = null)
    {
        return $this->instances->getInstance($name, $property);
    }

    /**
     * {@inheritDoc}
     */
    public function getReferences()
    {
        return $this->instances->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function setProviders(array $providers)
    {
        $this->processor->setProviders($providers);
    }

    /**
     * {@inheritDoc}
     */
    public function setReferences(array $instances)
    {
        $this->instances = new Collection($instances);
    }

    /**
     * parses a file at the given filename
     *
     * @param string filename
     * @return string data
     */
    protected function parseFile($filename)
    {
        $loader = $this;
        $includeWrapper = function() use ($filename, $loader) {
            ob_start();
            $res = include $filename;
            ob_end_clean();

            return $res;
        };

        $data = $includeWrapper();
        if (!is_array($data)) {
            throw new \UnexpectedValueException("Included file \"{$filename}\" must return an array of data");
        }
        return $data;
    }

    protected function buildInstances($data)
    {
        $instances = array();

        foreach ($data as $class => $specs) {
            $this->log('Loading '.$class);
            foreach ($specs as $name => $spec) {
                foreach ($this->instanceBuilders as $builder) {
                    if ($builder->canBuild($name)) {
                        $newInstances = $builder->build($class, $name, $spec);
                        if (is_array($newInstances)) {
                            $instances = array_merge($instances, $newInstances);
                        }
                        else {
                            $instances[] = $newInstances;
                        }
                        break;
                    }
                }
            }
        }
        
        return $instances;
    }

    private function populateObject($instance, $class, $name, $data)
    {
        $variables = array();

        if (isset($data['__set'])) {
            if (!method_exists($instance, $data['__set'])) {
                throw new \RuntimeException('Setter ' . $data['__set'] . ' not found in object');
            }
            $customSetter = $data['__set'];
            unset($data['__set']);
        }

        foreach ($data as $key => $val) {
            list($key, $flags) = FlagParser::parse($key);
            if (is_array($val) && '{' === key($val)) {
                throw new \RuntimeException('Misformatted string in object '.$name.', '.$key.'\'s value should be quoted if you used yaml');
            }

            if (isset($flags['unique'])) {
                $i = $uniqueTriesLimit = 128;

                do {
                    // process values
                    $generatedVal = $this->processor->process($val, $variables);

                    if (is_object($generatedVal)) {
                        $valHash = spl_object_hash($generatedVal);
                    } elseif (is_array($generatedVal)) {
                        $valHash = hash('md4', serialize($generatedVal));
                    } else {
                        $valHash = $generatedVal;
                    }
                } while (--$i > 0 && isset($this->uniqueValues[$class . $key][$valHash]));

                if (isset($this->uniqueValues[$class . $key][$valHash])) {
                    throw new \RuntimeException("Couldn't generate random unique value for $class: $key in $uniqueTriesLimit tries.");
                }

                $this->uniqueValues[$class . $key][$valHash] = true;
            } else {
                $generatedVal = $this->processor->process($val, $variables);
            }

            // add relations if available
            if (is_array($generatedVal) && $method = $this->findAdderMethod($instance, $key)) {
                foreach ($generatedVal as $rel) {
                    $rel = $this->typeHintChecker->check($instance, $method, $rel);
                    $instance->{$method}($rel);
                }
            } elseif (isset($customSetter)) {
                $instance->$customSetter($key, $generatedVal);
                $variables[$key] = $generatedVal;
            } elseif (is_array($generatedVal) && method_exists($instance, $key)) {
                foreach ($generatedVal as $num => $param) {
                    $generatedVal[$num] = $this->typeHintChecker->check($instance, $key, $param, $num);
                }
                call_user_func_array(array($instance, $key), $generatedVal);
                $variables[$key] = $generatedVal;
            } elseif (method_exists($instance, 'set'.$key)) {
                $generatedVal = $this->typeHintChecker->check($instance, 'set'.$key, $generatedVal);
                if(!is_callable(array($instance, 'set'.$key))) {
                    $refl = new \ReflectionMethod($instance, 'set'.$key);
                    $refl->setAccessible(true);
                    $refl->invoke($instance, $generatedVal);
                } else {
                    $instance->{'set'.$key}($generatedVal);
                }
                $variables[$key] = $generatedVal;
            } elseif (property_exists($instance, $key)) {
                $refl = new \ReflectionProperty($instance, $key);
                $refl->setAccessible(true);
                $refl->setValue($instance, $generatedVal);

                $variables[$key] = $generatedVal;
            } else {
                throw new \UnexpectedValueException('Could not determine how to assign '.$key.' to a '.$class.' object');
            }
        }
    }

    private function findAdderMethod($obj, $key)
    {
        if (method_exists($obj, $method = 'add'.$key)) {
            return $method;
        }

        if (class_exists('Symfony\Component\PropertyAccess\StringUtil') && method_exists('Symfony\Component\PropertyAccess\StringUtil', 'singularify')) {
            foreach ((array) StringUtil::singularify($key) as $singularForm) {
                if (method_exists($obj, $method = 'add'.$singularForm)) {
                    return $method;
                }
            }
        } elseif (class_exists('Symfony\Component\Form\Util\FormUtil') && method_exists('Symfony\Component\Form\Util\FormUtil', 'singularify')) {
            foreach ((array) FormUtil::singularify($key) as $singularForm) {
                if (method_exists($obj, $method = 'add'.$singularForm)) {
                    return $method;
                }
            }
        }

        if (method_exists($obj, $method = 'add'.rtrim($key, 's'))) {
            return $method;
        }

        if (substr($key, -3) === 'ies' && method_exists($obj, $method = 'add'.substr($key, 0, -3).'y')) {
            return $method;
        }
    }

    public function setORM(ORMInterface $manager)
    {
        $this->manager = $manager;
        $this->typeHintChecker->setORM($manager);
    }

    /**
     * Set the logger callable to execute with the log() method.
     *
     * @param callable|LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

   /**
     * Logs a message using the logger.
     *
     * @param string $message
     */
    public function log($message)
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->debug($message);
        } elseif ($logger = $this->logger) {
            $logger($message);
        }
    }
}
