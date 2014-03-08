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
 *              __extend: path_to_parent #optional
 *             property: value
 *             property2: value
 *         name2:
 *             [...]
 */
class Yaml extends Base
{

    /**
     * @param $file
     * @return array
     * @throws \UnexpectedValueException
     */
    public function parse($file)
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
        $data = $this->processExtend($data, $file);
        return $data;
    }

    /**
     * @param $data
     * @param $file
     * @return mixed
     */
    protected function processExtend($data, $file)
    {
        foreach ($data as $className => $fixtures) {
            if (is_array($fixtures) && !empty($fixtures)) {
                foreach ($fixtures as $fixtureName => $fixtureValues) {
                    if (isset($fixtureValues['__extend'])) {
                        $parentFile = dirname($file) . DIRECTORY_SEPARATOR . $fixtureValues['__extend'] . '.yml';
                        $parentData = $this->parse($parentFile);
                        $data[$className][$fixtureName] = array_merge($parentData, $fixtureValues);
                        unset($data[$className][$fixtureName]['__extend']);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function load($file)
    {
        $data = $this->parse($file);
        return parent::load($data);
    }
}
