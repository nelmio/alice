<?php

/*
 * This file is part of the Nelmio Fixture package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Fixture;

use Doctrine\Common\Persistence\ObjectManager;

class Fixture
{
    /**
     * Loads a fixture file into an object container
     *
     * @param string|array $file filename to load data from or data array
     * @param object       $container object container
     * @param array        $options available options:
     *                                - providers: an array of additional faker providers
     *                                - locale: the faker locale
     */
    public static function load($file, $container, array $options = array())
    {
        $defaults = array(
            'locale' => 'en_US',
            'providers' => array(),
        );
        $options = array_merge($defaults, $options);

        if (is_string($file) && preg_match('{\.ya?ml(\.php)?$}', $file)) {
            $loader = new Loader\Yaml($options['locale'], $options['providers']);
        } elseif ((is_string($file) && preg_match('{\.php$}', $file)) || is_array($file)) {
            $loader = new Loader\Base($options['locale'], $options['providers']);
        } else {
            throw new \InvalidValueException('Unknown file/data type: '.gettype($file).' ('.json_encode($file).')');
        }

        if ($container instanceof ObjectManager) {
            return $loader->load($file, new ORM\Doctrine($container));
        }

        throw new \InvalidValueException('Unknown container type '.get_class($container));
    }
}
