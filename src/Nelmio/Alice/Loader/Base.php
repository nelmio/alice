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
     * @var array
     */
    protected $references = array();

    /**
     * @var ORMInterface
     */
    protected $manager;

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
     * Custom faker providers to use with faker generator
     *
     * @var array
     */
    private $providers;

    /**
     * @var int
     */
    private $currentValue;

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
        $this->defaultLocale = $locale;
        $this->providers = $providers;

        if (is_numeric($seed)) {
            mt_srand($seed);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function load($data)
    {
        if (!is_array($data)) {
            // $loader is defined to give access to $loader->fake() in the included file's context
            $loader = $this;
            $filename = $data;
            $includeWrapper = function () use ($filename, $loader) {
                ob_start();
                $res = include $filename;
                ob_end_clean();

                return $res;
            };
            $data = $includeWrapper();
            if (!is_array($data)) {
                throw new \UnexpectedValueException('Included file "'.$filename.'" must return an array of data');
            }
        }

        $objects = array();

        foreach ($data as $class => $instances) {
            $this->log('Loading '.$class);
            list($class, $classFlags) = $this->parseFlags($class);
            foreach ($instances as $name => $spec) {
                if (preg_match('#\{([0-9]+)\.\.(\.?)([0-9]+)\}#i', $name, $match)) {
                    $from = $match[1];
                    $to = empty($match[2]) ? $match[3] : $match[3] - 1;
                    if ($from > $to) {
                        list($to, $from) = array($from, $to);
                    }
                    for ($i = $from; $i <= $to; $i++) {
                        $this->currentValue = $i;
                        $curName = str_replace($match[0], $i, $name);
                        list($curName, $instanceFlags) = $this->parseFlags($curName);
                        $objects[] = $this->createObject($class, $curName, $spec);
                    }
                    $this->currentValue = null;
                } elseif (preg_match('#\{([^,]+(\s*,\s*[^,]+)*)\}#', $name, $match)) {
                    $enumItems = array_map('trim', explode(',', $match[1]));
                    foreach ($enumItems as $item) {
                        $this->currentValue = $item;
                        $curName = str_replace($match[0], $item, $name);
                        list($curName, $instanceFlags) = $this->parseFlags($curName);
                        $objects[] = $this->createObject($class, $curName, $spec);
                    }
                    $this->currentValue = null;
                } else {
                    list($name, $instanceFlags) = $this->parseFlags($name);
                    $objects[] = $this->createObject($class, $name, $spec);
                }

                // remove the object from the object store if it is local only since it should not be persisted
                if (isset($classFlags['local']) || isset($instanceFlags['local'])) {
                    array_pop($objects);
                }
            }
        }

        return $objects;
    }

    /**
     * {@inheritDoc}
     */
    public function getReference($name, $property = null)
    {
        if (isset($this->references[$name])) {
            $reference = $this->references[$name];

            if ($property !== null) {
                if (property_exists($reference, $property)) {
                    $prop = new \ReflectionProperty($reference, $property);

                    if ($prop->isPublic()) {
                        return $reference->{$property};
                    }
                }

                $getter = 'get'.ucfirst($property);
                if (method_exists($reference, $getter) && is_callable(array($reference, $getter))) {
                    return $reference->$getter();
                }

                throw new \UnexpectedValueException('Property '.$property.' is not defined for reference '.$name);
            }

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

    public function fake($formatter, $locale = null, $arg = null, $arg2 = null, $arg3 = null)
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

    /**
     * {@inheritDoc}
     */
    public function setProviders(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     */
    public function setReferences(array $references)
    {
        $this->references = $references;
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

    private function parseFlags($key)
    {
        $flags = array();
        if (preg_match('{^(.+?)\s*\((.+)\)$}', $key, $matches)) {
            foreach (preg_split('{\s*,\s*}', $matches[2]) as $flag) {
                $val = true;
                if ($pos = strpos($flag, ':')) {
                    $flag = trim(substr($flag, 0, $pos));
                    $val = trim(substr($flag, $pos+1));
                }
                $flags[$flag] = $val;
            }
            $key = $matches[1];
        }

        return array($key, $flags);
    }

    private function createObject($class, $name, $data)
    {
        $obj = $this->createInstance($class, $name, $data);

        $variables = array();
        foreach ($data as $key => $val) {
            list($key, $flags) = $this->parseFlags($key);
            if (is_array($val) && '{' === key($val)) {
                throw new \RuntimeException('Misformatted string in object '.$name.', '.$key.'\'s value should be quoted if you used yaml');
            }

            if (isset($flags['unique'])) {
                $i = $uniqueTriesLimit = 128;

                do {
                    // process values
                    $generatedVal = $this->process($val, $variables);

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
                $generatedVal = $this->process($val, $variables);
            }

            // add relations if available
            if (is_array($generatedVal) && $method = $this->findAdderMethod($obj, $key)) {
                foreach ($generatedVal as $rel) {
                    $rel = $this->checkTypeHints($obj, $method, $rel);
                    $obj->{$method}($rel);
                }
            } elseif (is_array($generatedVal) && method_exists($obj, $key)) {
                foreach ($generatedVal as $num => $param) {
                    $generatedVal[$num] = $this->checkTypeHints($obj, $key, $param, $num);
                }
                call_user_func_array(array($obj, $key), $generatedVal);
                $variables[$key] = $generatedVal;
            } elseif (method_exists($obj, 'set'.$key)) {
                $generatedVal = $this->checkTypeHints($obj, 'set'.$key, $generatedVal);
                $obj->{'set'.$key}($generatedVal);
                $variables[$key] = $generatedVal;
            } elseif (property_exists($obj, $key)) {
                $refl = new \ReflectionProperty($obj, $key);
                $refl->setAccessible(true);
                $refl->setValue($obj, $generatedVal);

                $variables[$key] = $generatedVal;
            } else {
                throw new \UnexpectedValueException('Could not determine how to assign '.$key.' to a '.$class.' object');
            }
        }

        return $this->references[$name] = $obj;
    }

    private function createInstance($class, $name, array &$data)
    {
        try {
            // constructor is defined explicitly
            if (isset($data['__construct'])) {
                $args = $data['__construct'];
                unset($data['__construct']);

                // constructor override
                if (false === $args) {
                    if (version_compare(PHP_VERSION, '5.4', '<')) {
                        // unserialize hack for php <5.4
                        return unserialize(sprintf('O:%d:"%s":0:{}', strlen($class), $class));
                    }

                    $reflClass = new \ReflectionClass($class);

                    return $reflClass->newInstanceWithoutConstructor();
                }

                if (!is_array($args)) {
                    throw new \UnexpectedValueException('The __construct call in object '.$name.' must be defined as an array of arguments or false to bypass it');
                }

                // create object with given args
                $reflClass = new \ReflectionClass($class);
                $args = $this->process($args, array());
                foreach ($args as $num => $param) {
                    $args[$num] = $this->checkTypeHints($class, '__construct', $param, $num);
                }

                return $reflClass->newInstanceArgs($args);
            }

            // call the constructor if it contains optional params only
            $reflMethod = new \ReflectionMethod($class, '__construct');
            if (0 === $reflMethod->getNumberOfRequiredParameters()) {
                return new $class();
            }

            // exception otherwise
            throw new \RuntimeException('You must specify a __construct method with its arguments in object '.$name.' since class '.$class.' has mandatory constructor arguments');
        } catch (\ReflectionException $exception) {
            return new $class();
        }
    }

    /**
     * Checks if the value is typehinted with a class and if the current value can be coerced into that type
     *
     * It can either convert to datetime or attempt to fetched from the db by id
     *
     * @param  mixed   $obj    instance or class name
     * @param  string  $method
     * @param  string  $value
     * @param  integer $pNum
     * @return mixed
     */
    private function checkTypeHints($obj, $method, $value, $pNum = 0)
    {
        if (!is_numeric($value) && !is_string($value)) {
            return $value;
        }

        $reflection = new \ReflectionMethod($obj, $method);
        $params = $reflection->getParameters();

        if (!$params[$pNum]->getClass()) {
            return $value;
        }

        $hintedClass = $params[$pNum]->getClass()->getName();

        if ($hintedClass === 'DateTime') {
            try {
                if (preg_match('{^[0-9]+$}', $value)) {
                    $value = '@'.$value;
                }

                return new \DateTime($value);
            } catch (\Exception $e) {
                throw new \UnexpectedValueException('Could not convert '.$value.' to DateTime for '.$reflection->getDeclaringClass()->getName().'::'.$method, 0, $e);
            }
        }

        if ($hintedClass) {
            if (!$this->manager) {
                throw new \LogicException('To reference objects by id you must first set a Nelmio\Alice\ORMInterface object on this instance');
            }
            $value = $this->manager->find($hintedClass, $value);
        }

        return $value;
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
                if (isset($variables[$match[1]])) {
                    return '$variables['.var_export($match[1], true).']';
                }

                return $match[0];
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
                $data = $this->getRandomReferences($matches['reference'], $multi, $property);
            } else {
                if (null !== $multi) {
                    throw new \UnexpectedValueException('To use multiple references you must use a mask like "'.$matches['multi'].'x @user*", otherwise you would always get only one item.');
                }
                $data = $this->getReference($matches['reference'], $property);
            }
        }

        return $data;
    }

    private function getRandomReferences($mask, $count = 1, $property = null)
    {
        if ($count === 0) {
            return array();
        }

        $availableRefs = array();
        foreach ($this->references as $key => $val) {
            if (preg_match('{^'.str_replace('*', '.+', $mask).'$}', $key)) {
                $availableRefs[] = $key;
            }
        }

        if (!$availableRefs) {
            throw new \UnexpectedValueException('Reference mask "'.$mask.'" did not match any existing reference, make sure the object is created after its references');
        }

        if (null === $count) {
            return $this->getReference($availableRefs[mt_rand(0, count($availableRefs) - 1)], $property);
        }

        $res = array();
        while ($count-- && $availableRefs) {
            $ref = array_splice($availableRefs, mt_rand(0, count($availableRefs) - 1), 1);
            $res[] = $this->getReference(current($ref), $property);
        }

        return $res;
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
