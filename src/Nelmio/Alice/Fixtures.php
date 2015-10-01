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
use Nelmio\Alice\Persister\Doctrine;
use Psr\Log\LoggerInterface;
use Nelmio\Alice\Fixtures\Loader;

class Fixtures
{
    /**
     * @var Loader[]
     */
    private static $loaders = [];

    /**
     * @var PersisterInterface
     */
    protected $persister;

    /**
     * @var array
     */
    protected $defaultOptions = [
            'locale' => 'en_US',
            'providers' => [],
            'seed' => 1,
            'logger' => null,
            'persist_once' => false,
    ];

    /**
     * @var ProcessorInterface[]
     */
    protected $processors;

    /**
     * @param PersisterInterface $persister
     * @param array              $defaultOptions
     * @param array              $processors
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(PersisterInterface $persister, array $defaultOptions = [], array $processors = [])
    {
        $this->persister = $persister;

        $this->validateOptions($defaultOptions);
        $this->defaultOptions = array_merge($this->defaultOptions, $defaultOptions);

        foreach ($processors as $processor) {
            if (false === $processor instanceof ProcessorInterface) {
                throw new \InvalidArgumentException(
                    'Expected processor to implement Nelmio\Alice\Fixtures\ProcessorInterface.'
                );
            }
        }
        $this->processors = $processors;
    }

    /**
     * Loads a fixture file into an object persister.
     *
     * @param string|array $files      filename, glob mask (e.g. *.yml) or array of filenames to load data from, or data array
     * @param object       $persister  object persister
     * @param array        $options    available options:
     *                                 - providers: an array of additional faker providers
     *                                 - locale: the faker locale
     *                                 - seed: a seed to make sure faker generates data consistently across
     *                                 runs, set to null to disable
     *                                 - logger: a callable or Psr\Log\LoggerInterface object that will receive progress information
     *                                 - persist_once: only persist objects once if multiple files are passed
     * @param array        $processors optional array of ProcessorInterface instances
     */
    public static function load($files, $persister, array $options = [], array $processors = [])
    {
        $_persister = null;

        switch (true) {
            case $persister instanceof PersisterInterface:
                $_persister = $persister;
                break;

            case $persister instanceof ObjectManager:
                $_persister = new Doctrine($persister);
                break;

            default:
                throw new \InvalidArgumentException('Unknown persister type '.get_class($persister));
        }

        $fixtures = new static($_persister, $options, $processors);

        return $fixtures->loadFiles($files);
    }

    /**
     * @param       $files
     * @param array $options
     *
     * @return array
     */
    public function loadFiles($files, array $options = [])
    {
        $this->validateOptions($options);
        $options = array_merge($this->defaultOptions, $options);

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
            $files = [$files];
        }

        $objects = [];
        foreach ($files as $file) {
            $loader = self::getLoader($options);

            if (isset($options['logger'])) {
                $loader->setLogger($options['logger']);
            }

            $loader->setPersister($this->persister);
            $set = $loader->load($file);

            if (!$options['persist_once']) {
                $this->persist($this->persister, $set);
            }

            $objects = array_merge($objects, $set);
        }

        if ($options['persist_once']) {
            $this->persist($this->persister, $objects);
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

    /**
     * Checks if the options are valid or not. If not throws an exception.
     *
     * @param array $options
     *
     * @throws \InvalidArgumentException
     */
    private function validateOptions(array $options)
    {
        foreach (array_keys($options) as $key) {
            if (false === array_key_exists($key, $this->defaultOptions)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown key "%s", expected: %s',
                    $key,
                    implode(', ', array_keys($this->defaultOptions))
                ));
            }
        }

        if (isset($options['providers'])) {
            $providers = $options['providers'];

            if (false === is_array($providers)) {
                throw new \InvalidArgumentException('Expected "providers" option value to be an array');
            }

            foreach ($providers as $provider) {
                if (false === is_object($provider) && false === is_string($provider)) {
                    throw new \InvalidArgumentException(sprintf(
                        'The provider should be a string or an object, got %s instead',
                        is_scalar($provider) ? $provider : gettype($provider)
                    ));
                }
            }
        }

        if (isset($options['logger'])) {
            $logger = $options['logger'];

            if (false === is_callable($logger) && false === $logger instanceof LoggerInterface) {
                throw new \InvalidArgumentException(
                    'Expected "logger" option value to be a callable or to implement Psr\Log\LoggerInterface'
                );
            }
        }

        if (isset($options['persist_once'])) {
            if (false === is_bool($options['persist_once'])) {
                throw new \InvalidArgumentException('Expected "persist_once" option value value to be a boolean.');
            }
        }
    }
}
