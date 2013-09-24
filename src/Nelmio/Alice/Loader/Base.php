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
use Nelmio\Alice\Provider\IdentityProvider;

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
     * @var array
     */
    protected $templates = array();

    /**
     * @var array
     */
    protected $incompleteInstances = array();

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
     * @var boolean
     */
    private $allowForwardReferences = false;

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
        $this->providers = array_merge($this->getBuiltInProviders(), $providers);

        if (is_numeric($seed)) {
            mt_srand($seed);
        }
    }

    private function getBuiltInProviders()
    {
        return array(new IdentityProvider());
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

        // create instances
        $instances = array();
        foreach ($data as $class => $specs) {
            $this->log('Loading '.$class);
            list($class, $classFlags) = $this->parseFlags($class);
            foreach ($specs as $name => $spec) {
                if (preg_match('#\{([0-9]+)\.\.(\.?)([0-9]+)\}#i', $name, $match)) {
                    $from = $match[1];
                    $to = empty($match[2]) ? $match[3] : $match[3] - 1;
                    if ($from > $to) {
                        list($to, $from) = array($from, $to);
                    }
                    for ($i = $from; $i <= $to; $i++) {
                        $curSpec = $spec;
                        $curName = str_replace($match[0], $i, $name);
                        list($curName, $instanceFlags) = $this->parseFlags($curName);
                        if (!empty($instanceFlags)) {
                            // Reverse flag order: check templates from last to first, so that last one wins
                            foreach (array_reverse(array_keys($instanceFlags)) as $flag) {
                                if (preg_match('#^extends\s*(.+)$#', $flag, $match2)) {
                                    $template = $this->getTemplate($match2[1]);
                                    $curSpec = array_merge($template, $curSpec);
                                }
                            }
                        }
                        $this->currentValue = $i;
                        $instances[$curName] = array($this->createInstance($class, $curName, $curSpec), $class, $curName, $curSpec, $classFlags, $instanceFlags, $i);
                        $this->currentValue = null;
                    }
                } elseif (preg_match('#\{([^,]+(\s*,\s*[^,]+)*)\}#', $name, $match)) {
                    $enumItems = array_map('trim', explode(',', $match[1]));
                    foreach ($enumItems as $item) {
                        $curSpec = $spec;
                        $curName = str_replace($match[0], $item, $name);
                        list($curName, $instanceFlags) = $this->parseFlags($curName);
                        $this->currentValue = $item;
                        $instances[$curName] = array($this->createInstance($class, $curName, $curSpec), $class, $curName, $curSpec, $classFlags, $instanceFlags, $item);
                        $this->currentValue = null;
                    }
                } else {
                    list($name, $instanceFlags) = $this->parseFlags($name);
                    if (!empty($instanceFlags)) {
                        // Reverse flag order: check templates from last to first, so that last one wins
                        foreach (array_reverse(array_keys($instanceFlags)) as $flag) {
                            if(preg_match('#^extends\s*(.+)$#', $flag, $match)) {
                                $template = $this->getTemplate($match[1]);
                                $spec = array_merge($template, $spec);
                            }
                        }
                    }
                    if (isset($instanceFlags['template'])) {
                        $this->templates[$name] = $spec;
                        continue;
                    }
                    $instances[$name] = array($this->createInstance($class, $name, $spec), $class, $name, $spec, $classFlags, $instanceFlags, null);
                }
            }
        }

        // populate instances
        $instances = array_merge($instances, $this->incompleteInstances);
        $this->incompleteInstances = array();
        $objects = array();
        foreach ($instances as $instanceName => $instanceData) {
            list($instance, $class, $name, $spec, $classFlags, $instanceFlags, $curValue) = $instanceData;

            $this->currentValue = $curValue;

            try {
                $this->populateObject($instance, $class, $name, $spec);
                $this->currentValue = null;

                // add the object in the object store unless it's local
                if (!isset($classFlags['local']) && !isset($instanceFlags['local'])) {
                    $objects[$instanceName] = $instance;
                }
            } catch (MissingReferenceException $e) {
                if (!$this->allowForwardReferences) {
                    throw $e;
                }

                $instanceData[] = $e;
                $this->incompleteInstances[] = $instanceData;
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

        throw new MissingReferenceException('Reference '.$name.' is not defined');
    }

    /**
     * {@inheritDoc}
     */
    public function getReferences()
    {
        return $this->references;
    }

    public function getIncompleteInstances()
    {
        return $this->incompleteInstances;
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
        $this->emptyGenerators();
    }

    /**
     * {@inheritDoc}
     */
    public function setReferences(array $references)
    {
        $this->references = $references;
    }

    protected function createInstance($class, $name, array &$data)
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
                        return $this->references[$name] = unserialize(sprintf('O:%d:"%s":0:{}', strlen($class), $class));
                    }

                    $reflClass = new \ReflectionClass($class);

                    return $this->references[$name] = $reflClass->newInstanceWithoutConstructor();
                }

                /**
                 * Sequential arrays call the constructor, hashes call a static method
                 *
                 * array('foo', 'bar') => new $class('foo', 'bar')
                 * array('foo' => array('bar')) => $class::foo('bar')
                 */
                if (is_array($args)) {
                    $constructor = '__construct';
                    list($index, $values) = each($args);
                    if ($index !== 0) {
                        if (!is_array($values)) {
                            throw new \UnexpectedValueException("The static '$index' call in object '$name' must be given an array");
                        }
                        if (!is_callable(array($class, $index))) {
                            throw new \UnexpectedValueException("Cannot call static method '$index' on class '$class' as a constructor for object '$name'");
                        }
                        $constructor = $index;
                        $args = $values;
                    }
                } else {
                    throw new \UnexpectedValueException('The __construct call in object '.$name.' must be defined as an array of arguments or false to bypass it');
                }

                // create object with given args
                $reflClass = new \ReflectionClass($class);
                $args = $this->process($args, array());
                foreach ($args as $num => $param) {
                    $args[$num] = $this->checkTypeHints($class, $constructor, $param, $num);
                }

                if ($constructor === '__construct') {
                    $instance = $reflClass->newInstanceArgs($args);
                } else {
                    $instance = forward_static_call_array(array($class, $constructor), $args);
                    if (!($instance instanceof $class)) {
                        throw new \UnexpectedValueException("The static constructor '$constructor' for object '$name' returned an object that is not an instance of '$class'");
                    }
                }

                return $this->references[$name] = $instance;
            }

            // call the constructor if it contains optional params only
            $reflMethod = new \ReflectionMethod($class, '__construct');
            if (0 === $reflMethod->getNumberOfRequiredParameters()) {
                return $this->references[$name] = new $class();
            }

            // exception otherwise
            throw new \RuntimeException('You must specify a __construct method with its arguments in object '.$name.' since class '.$class.' has mandatory constructor arguments');
        } catch (\ReflectionException $exception) {
            return $this->references[$name] = new $class();
        }
    }

    private function getTemplate($name)
    {
        if (!array_key_exists($name, $this->templates)) {
            throw new \UnexpectedValueException('Template '.$name.' is not defined.');
        }

        return $this->templates[$name];
    }

    private function emptyGenerators()
    {
        $this->generators = array();
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

        $this->references['self'] = $instance;

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
            if (is_array($generatedVal) && $method = $this->findAdderMethod($instance, $key)) {
                foreach ($generatedVal as $rel) {
                    $rel = $this->checkTypeHints($instance, $method, $rel);
                    $instance->{$method}($rel);
                }
            } elseif (isset($customSetter)) {
                $instance->$customSetter($key, $generatedVal);
                $variables[$key] = $generatedVal;
            } elseif (is_array($generatedVal) && method_exists($instance, $key)) {
                foreach ($generatedVal as $num => $param) {
                    $generatedVal[$num] = $this->checkTypeHints($instance, $key, $param, $num);
                }
                call_user_func_array(array($instance, $key), $generatedVal);
                $variables[$key] = $generatedVal;
            } elseif (method_exists($instance, 'set'.$key)) {
                $generatedVal = $this->checkTypeHints($instance, 'set'.$key, $generatedVal);
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

        unset($this->references['self']);
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

            if (trim($matches['name']) == '') {
                $matches['name'] = 'identity';
            }

            if (!$args) {
                return $that->fake($matches['name'], $matches['locale']);
            }

            // replace references to other variables in the same object
            $args = preg_replace_callback('{\{?\$([a-z0-9_]+)\}?}i', function ($match) use ($variables) {
                if (array_key_exists($match[1], $variables)) {
                    return '$variables['.var_export($match[1], true).']';
                }

                return $match[0];
            }, $args);

            // replace references to other objects
            $args = preg_replace_callback('{(?<string>".*?[^\\\\]")|(?:(?<multi>\d+)x )?(?<!\\\\)@(?<reference>[a-z0-9_.*]+)(?:\->(?<property>[a-z0-9_-]+))?}i', function ($match) use ($that, $args) {

                if (!empty($match['string'])) {
                    return $match['string'];
                }

                $multi    = ('' !== $match['multi']) ? $match['multi'] : null;
                $property = isset($match['property']) ? $match['property'] : null;
                if (strpos($match['reference'], '*')) {
                    return '$that->getRandomReferences(' . var_export($match['reference'], true) . ', ' . var_export($multi, true) . ', ' . var_export($property, true) . ')';
                }
                if (null !== $multi) {
                    throw new \UnexpectedValueException('To use multiple references you must use a mask like "'.$match['multi'].'x @user*", otherwise you would always get only one item.');
                }
                return '$that->getReference(' . var_export($match['reference'], true) . ', ' . var_export($property, true) . ')';
            }, $args);

            $locale = var_export($matches['locale'], true);
            $name = var_export($matches['name'], true);

            return eval('return $that->fake(' . $name . ', ' . $locale . ', ' . $args . ');');
        };

        // format placeholders without preg_replace if there is only one to avoid __toString() being called
        $placeHolderRegex = '<(?:(?<locale>[a-z]+(?:_[a-z]+)?):)?(?<name>[a-z0-9_]+?)?\((?<args>(?:[^)]*|\)(?!>))*)\)>';
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

        // unescape at-signs
        if (is_string($data) && false !== strpos($data, '\\')) {
            $data = preg_replace('{\\\\([@\\\\])}', '$1', $data);
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

    public function enableForwardReferences($val = true)
    {
        $this->allowForwardReferences = !($val === false);
    }
}
