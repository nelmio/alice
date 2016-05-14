<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Parser\Methods;

use Nelmio\Alice\Fixtures\Loader;
use Symfony\Component\Yaml\Yaml as YamlParser;

/**
 * Parses data from a yaml file
 *
 * The yaml file can contain PHP which will be executed before it is parsed as yaml.
 * PHP in the yaml file has access to $context->fake() to generate data
 *
 * The general format of the file must follow this example:
 *
 *         Namespace\Class:
 *                 name:
 *                         property: value
 *                         property2: value
 *                 name2:
 *                         [...]
 */
class Yaml extends Base
{
    /**
     * {@inheritDoc}
     **/
    protected $extension = 'ya?ml';

    /**
     * {@inheritDoc}
     *
     * @throws \UnexpectedValueException
     */
    public function parse($file)
    {
        $yaml = $this->compilePhp($file);
        $data = YamlParser::parse($yaml);

        if (!is_array($data)) {
            throw new \UnexpectedValueException('Yaml files must parse to an array of data');
        }

        $data = $this->processIncludes($data, $file);
        $data = $this->processParameters($data);

        return $data;
    }
}
