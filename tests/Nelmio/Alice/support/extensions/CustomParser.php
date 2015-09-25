<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\support\extensions;

use Nelmio\Alice\Fixtures\Parser\Methods\Base;

class CustomParser extends Base
{
    /**
     * @var string
     **/
    protected $extension = 'csv';

    /**
     * this custom parser roughly parses a CSV.
     */
    public function parse($file)
    {
        $result = [];

        $csv = $this->compilePhp($file);

        $rows = explode("\n", str_replace("\r\n", "\n", trim($csv, "\r\n")));
        $result[$class = array_shift($rows)] = [];

        foreach ($rows as $row) {
            $properties = explode(',', $row);
            $result[$class][$name = array_shift($properties)] = [];

            foreach ($properties as $property) {
                $propertyPieces = explode(':', $property);
                $result[$class][$name][$propertyPieces[0]] = $propertyPieces[1];
            }
        }

        return $result;
    }
}
