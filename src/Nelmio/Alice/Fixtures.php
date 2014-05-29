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

use Nelmio\Alice\Event\PersistEvent;
use Nelmio\Alice\Loader\LoaderInterface;
use Nelmio\Alice\Loader\PersistenceAwareLoaderInterface;
use Nelmio\Alice\Persister\PersisterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Fixtures
{
    private static $loaders = array();

    protected $persister;
    protected $dispatcher;

    protected $defaultOptions;

    public function __construct(PersisterInterface $persister, EventDispatcherInterface $dispatcher, array $defaultOptions = array())
    {
        $this->persister = $persister;
        $this->dispatcher = $dispatcher;

        $defaults = array(
            'locale' => 'en_US',
            'providers' => array(),
            'seed' => 1,
            'logger' => null,
            'persist_once' => false,
        );
        $this->defaultOptions = array_merge($defaults, $defaultOptions);
    }

    /**
     * @param string|array             $loadable   filename, glob mask (e.g. *.yml) or array of filenames to load data from, or data array
     * @param PersisterInterface       $persister
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $options    Options when loading fixtures, available options:
     *                                             - providers: an array of additional faker providers
     *                                             - locale: the faker locale
     *                                             - seed: a seed to make sure faker generates data consistently across runs, set to null to disable
     *                                             - logger: a callable or Psr\Log\LoggerInterface object that will receive progress information
     *                                             - persist_once: only persist objects once if multiple files are passsed
     *
     * @return mixed
     */
    public static function load($loadable, PersisterInterface $persister, EventDispatcherInterface $dispatcher, array $options = array())
    {
        $fixtures = new static($persister, $dispatcher, $options);

        return $fixtures->loadFiles($loadable);
    }

    public function loadFiles($files, array $options = array())
    {
        $options = array_merge($this->defaultOptions, $options);

        // glob strings to filenames
        if (!is_array($files)) {
            $files = glob($files, GLOB_BRACE);
        }

        // wrap the data array in an array of one data array
        if (!is_string(current($files))) {
            $files = array($files);
        }

        $objects = array();
        foreach ($files as $file) {
            $loader = $this->getLoaderForFile($file, $options);

            if ($loader instanceof PersistenceAwareLoaderInterface) {
                $loader->setPersister($this->persister);
            }

            $set = $loader->load($file);

            if (!$options['persist_once']) {
                $this->persist($set);
            }

            $objects = array_merge($objects, $set);
        }

        if ($options['persist_once']) {
            $this->persist($objects);
        }

        return $objects;
    }

    /**
     * @param string $file
     * @param array $options
     *
     * @return LoaderInterface
     *
     * @throws \InvalidArgumentException
     */
    private function getLoaderForFile($file, array $options)
    {
        if (is_string($file) && preg_match('{\.ya?ml(\.php)?$}', $file)) {
            $loader = self::getLoader('Yaml', $options);
        } elseif ((is_string($file) && preg_match('{\.php$}', $file)) || is_array($file)) {
            $loader = self::getLoader('Base', $options);
        } else {
            throw new \InvalidArgumentException('Unknown file/data type: '.gettype($file).' ('.json_encode($file).')');
        }

        return $loader;
    }

    private function persist($objects)
    {
        $prePersist = new PersistEvent($objects);
        $this->dispatcher->dispatch(Events::PRE_PROCESS, $prePersist);

        $this->persister->persist($objects);

        $postPersist = new PersistEvent($objects);
        $this->dispatcher->dispatch(Events::POST_PROCESS, $postPersist);
    }

    private static function generateLoaderKey($class, array $options)
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
            '%s_%s_%s_%s',
            $class,
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

    /**
     * Returns a loader of the given class based on the given options.
     *
     * @param string $class
     * @param array $options
     *
     * @return LoaderInterface
     */
    private static function getLoader($class, array $options)
    {
        // Generate an array key based not only on the loader's class - but also
        // on the options, so that separate loaders will be created when we want
        // to load several fixtures that use different custom providers.
        $loaderKey = self::generateLoaderKey($class, $options);
        if (!isset(self::$loaders[$loaderKey])) {
            $fqcn = 'Nelmio\Alice\Loader\\'.$class;
            $loader = new $fqcn($options['locale'], $options['providers'], $options['seed']);

            if ($loader instanceof LoggerAwareInterface and $options['logger'] instanceof LoggerInterface) {
                $loader->setLogger($options['logger']);
            }

            self::$loaders[$loaderKey] = $loader;
        }

        return self::$loaders[$loaderKey];
    }
}
