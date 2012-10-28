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
 * Loads fixtures from a yaml file
 *
 * The yaml file can contain PHP which will be executed before it is parsed as yaml.
 * PHP in the yaml file has access to $loader->fake() to generate data
 *
 * The general format of the file must follow this example:
 *
 *     Namespace\Class:
 *         name:
 *             property: value
 *             property2: value
 *         name2:
 *             [...]
 */
class Yaml extends Base
{
    /**
     * {@inheritDoc}
     */
    public function load($file)
    {
        ob_start();
        $loader = $this;
        $includeWrapper = function () use ($file, $loader) {
            return include $file;
        };
        $data = $includeWrapper();
        if (true !== $data) {
            $yaml = ob_get_clean();
            $data = YamlParser::parse($yaml);
        }

        if (!is_array($data)) {
            throw new \UnexpectedValueException('Yaml files must parse to an array of data');
        }

        return parent::load($data);
    }
}
