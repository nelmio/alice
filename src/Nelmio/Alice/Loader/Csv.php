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
class Csv extends Base
{
    /**
     * {@inheritDoc}
     */
    public function load($file, $entity = null)
    {
        $csv = new \parseCSV();
        $csv->auto($file);
        $data = array($entity => array());
        $spaces = explode('\\', $entity);
        $class = end($spaces);
        foreach ($csv->data as $i => $row)
        {
            $ref = $class . ++$i;
            if ( !empty($row['_ref']) ) {
                $ref = $row['_ref'];
            }
            unset($row['_ref']);
            $data[$entity][$ref] = $row;
        }
        return parent::load($data);
    }
}
