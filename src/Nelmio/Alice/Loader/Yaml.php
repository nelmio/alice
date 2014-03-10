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
     * @param $file
     * @return array
     * @throws \UnexpectedValueException
     */
    public function parse($file)
    {
        ob_start();
        $loader = $this;

        // isolates the file from current context variables and gives
        // it access to the $loader object to inline php blocks if needed
        $includeWrapper = function () use ($file, $loader) {
            return include $file;
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
        $data = $this->processInclude($data, $file);
        return $data;
    }

    /**
     * @param $data
     * @param $file
     * @return mixed
     */
    protected function processInclude($data, $file)
    {
        if (isset($data['include'])) {
            foreach ($data['include'] as $include) {
                $includeFile = dirname($file) . DIRECTORY_SEPARATOR . $include;
                $includeData = $this->parse($includeFile);
                $this->mergeIncludeData($data, $includeData);
            }
        }
        unset($data['include']);
        foreach ($data as $class => $fixtures) {
            $data[$class] = array_reverse($fixtures);
        }

        return $data;
    }

    /**
     * @param $data
     * @param $includeData
     */
    private function mergeIncludeData(&$data, &$includeData)
    {
        foreach ($includeData as $child => $value) {
            if (isset($data[$child])) {
                if (is_array($data[$child]) && is_array($value)) {
                    $this->mergeIncludeData($data[$child], $value);
                }
            } else {
                $data[$child] = $value;
            }
        }
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
