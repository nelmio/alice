<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

use Nelmio\Alice\Fixtures\Loader;

class Fixtures
{
    private static $loaders = array();

    protected $container;
    protected $defaultOptions;
    protected $processors;

    public function __construct($container, array $defaultOptions = array(), array $processors = array())
    {
        $this->container = $container;
        $defaults = array(
            'locale' => 'en_US',
            'providers' => array(),
            'seed' => 1,
            'logger' => null,
            'persist_once' => false,
        );
        $this->defaultOptions = array_merge($defaults, $defaultOptions);
        $this->processors = $processors;
    }

    /**
     * Loads a fixture file into an object container
     *
     * @param string|array $file       filename, glob mask (e.g. *.yml) or array of filenames to load data from, or data array
     * @param object       $container  object container
     * @param array        $options    available options:
     *                                 - providers: an array of additional faker providers
     *                                 - locale: the faker locale
     *                                 - seed: a seed to make sure faker generates data consistently across
     *                                 runs, set to null to disable
     *                                 - logger: a callable or Psr\Log\LoggerInterface object that will receive progress information
     *                                 - persist_once: only persist objects once if multiple files are passsed
     * @param array        $processors optional array of ProcessorInterface instances
     */
    public static function load($files, $container, array $options = array(), array $processors = array())
    {
        $fixtures = new static($container, $options, $processors);

        return $fixtures->loadFiles($files);
    }

    public function loadFiles($files, array $options = array())
    {
        $options = array_merge($this->defaultOptions, $options);

        if ($this->container instanceof ObjectManager) {
            $persister = new ORM\Doctrine($this->container);
        } else {
            throw new \InvalidArgumentException('Unknown container type '.get_class($this->container));
        }

        // glob strings to filenames
        if (!is_array($files)) {
            $matches = glob($files, GLOB_BRACE);
            if (!$matches && !file_exists($files)) {
                throw new \InvalidArgumentException('The file could not be found: '.$files);
            }
            $files = $matches;
        }

        // wrap the data array in an array of one data array
        if (!is_string(current($files))) {
            $files = array($files);
        }

        $objects = array();
        foreach ($files as $file) {
            $loader = self::getLoader($options);

            if (is_callable($options['logger']) || $options['logger'] instanceof LoggerInterface) {
                $loader->setLogger($options['logger']);
            } elseif (null !== $options['logger']) {
                throw new \RuntimeException('Logger must be callable or an instance of Psr\Log\LoggerInterface.');
            }

            $loader->setORM($persister);
            $set = $loader->load($file);

            if (!$options['persist_once']) {
                $this->persist($persister, $set);
            }

            $objects = array_merge($objects, $set);
        }

        if ($options['persist_once']) {
            $this->persist($persister, $objects);
        }

        return $objects;
    }

    public function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    protected function persist($persister, $objects)
    {
        foreach ($this->processors as $proc) {
            foreach ($objects as $obj) {
                $proc->preProcess($obj);
            }
        }

        $persister->persist($objects);

        foreach ($this->processors as $proc) {
            foreach ($objects as $obj) {
                $proc->postProcess($obj);
            }
        }
    }

    private static function generateLoaderKey(array $options)
    {
        $providers = '';
        if (!empty($options['providers'])) {
            foreach ($options['providers'] as $item) {
                if (is_object($item)) {
                    $item = get_class($item);
                } elseif (!is_string($item)) {
                    $msg = 'The provider should be a string or an object, got '
                           . (is_scalar($item) ? $item : gettype($item))
                            . ' instead';
                    throw new \InvalidArgumentException($msg);
                }

                // turn all of the class names into fully-qualified ones
                $item = '\\' . ltrim($item, '\\');

                $providers .= $item;
            }
        }

        return sprintf(
            '%s_%s_%s',
            (is_numeric($options['seed'])
             ? strval($options['seed'])
             : gettype($options['seed'])
            ),
            $options['locale'],
            (!strlen($providers)
             ? ''
             : md5($providers)
            )
        );
    }

    private static function getLoader(array $options)
    {
        // Generate an array key based on the options, so that separate loaders
        // will be created when we want to load several fixtures that use different
        // custom providers.
        $loaderKey = self::generateLoaderKey($options);
        if (!isset(self::$loaders[$loaderKey])) {
            self::$loaders[$loaderKey] = new Loader($options['locale'], $options['providers'], $options['seed']);
        }

        return self::$loaders[$loaderKey];
    }
}
