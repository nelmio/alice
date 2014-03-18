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

use Symfony\Component\Yaml\Yaml as YamlParser;

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
    protected function parseFile($filename)
    {
        $loader = $this;

        ob_start();
        $includeWrapper = function () use ($filename, $loader) {
            return include $filename;
        };
        $data = $includeWrapper();

        if (1 === $data) {
            // include didn't return data but included correctly, parse it as yaml
            $yaml = ob_get_clean();
            $data = YamlParser::parse($yaml);
        } else {
            // make sure to clean up if theres a failure
            ob_end_clean();
        }

        if (!is_array($data)) {
            throw new \UnexpectedValueException('Yaml files must parse to an array of data');
        }

        return $data;
    }
}
