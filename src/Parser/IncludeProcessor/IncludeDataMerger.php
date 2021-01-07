<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Parser\IncludeProcessor;

use Nelmio\Alice\IsAServiceTrait;

final class IncludeDataMerger
{
    use IsAServiceTrait;

    /**
     * Merges a parsed file data with another. If some data overlaps, the existent data is kept, i.e. the included data
     * is discarded.
     *
     * @param array $data        Parsed file data
     * @param array $includeData Parsed file data to merge
     */
    public function mergeInclude(array $data, array $includeData): array
    {
        foreach ($includeData as $class => $fixtures) {
            // $class is either a FQCN or 'parameters'
            if (array_key_exists($class, $data)) {
                if (is_array($data[$class]) && is_array($fixtures)) {
                    foreach ($fixtures as $key => $fixture) {
                        if (!array_key_exists($key, $data[$class])) {
                            $data[$class][$key] = $fixture;
                        }
                    }
                }
            } else {
                $data[$class] = $fixtures;
            }
        }

        return $data;
    }
}
