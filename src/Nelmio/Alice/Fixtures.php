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

class Fixtures
{
    private static $loaders = array();

    /**
     * Loads a fixture file into an object container
     *
     * @param string|array $file      filename, glob mask (e.g. *.yml) or array of filenames to load data from, or data array
     * @param object       $container object container
     * @param array        $options   available options:
     *                                - providers: an array of additional faker providers
     *                                - locale: the faker locale
     *                                - seed: a seed to make sure faker generates data consistently across
     *                                  runs, set to null to disable
     */
    public static function load($files, $container, array $options = array())
    {
        $defaults = array(
            'locale' => 'en_US',
            'providers' => array(),
            'seed' => 1,
        );
        $options = array_merge($defaults, $options);

        if ($container instanceof ObjectManager) {
            $persister = new ORM\Doctrine($container);
        } else {
            throw new \InvalidArgumentException('Unknown container type '.get_class($container));
        }

        // glob strings to filenames
        if (!is_array($files)) {
            $files = glob($files);
        }

        // wrap the data array in an array of one data array
        if (!is_string(current($files))) {
            $files = array($files);
        }

        $objects = array();
        foreach ($files as $file) {
            if (is_string($file) && preg_match('{\.ya?ml(\.php)?$}', $file)) {
                $loader = self::getLoader('Yaml', $options);
            } elseif ((is_string($file) && preg_match('{\.php$}', $file)) || is_array($file)) {
                $loader = self::getLoader('Base', $options);
            } else {
                throw new \InvalidArgumentException('Unknown file/data type: '.gettype($file).' ('.json_encode($file).')');
            }

            $loader->setORM($persister);
            $set = $loader->load($file);
            $persister->persist($set);

            $objects = array_merge($objects, $set);
        }

        return $objects;
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

    private static function getLoader($class, array $options)
    {
        // Generate an array key based not only on the loader's class - but also
        // on the options, so that separate loaders will be created when we want
        // to load several fixtures that use different custom providers.
        $loaderKey = self::generateLoaderKey($class, $options);
        if (!isset(self::$loaders[$loaderKey])) {
            $fqcn = 'Nelmio\Alice\Loader\\'.$class;
            self::$loaders[$loaderKey] = new $fqcn($options['locale'], $options['providers'], $options['seed']);
        }

        return self::$loaders[$loaderKey];
    }
}
