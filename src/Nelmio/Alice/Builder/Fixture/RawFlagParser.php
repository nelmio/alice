<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Fixture;

final class RawFlagParser
{
    /**
     * Parses a string element to extract potential flags from it.
     *
     * @example
     *  parse('dummy (extends base_dummy, template')
     *  => [
     *      'dummy',
     *      [
     *          'extends base_dummy',
     *          'template',
     *      ]
     *  ]
     *
     * @param string $element
     *
     * @throws \InvalidArgumentException
     *
     * @return array <string, string[]> The first string is the
     */
    public function parse(string $element): array
    {
        if (1 === preg_match('/(?<key>.+?)\s*\((?<flags>.+)\).*/', $element, $matches)) {
            if (' ' === $key = $matches['key']) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'No key found for the element "%s"',
                        $element
                    )
                );
            }

            return [
                $key,
                preg_split('/\s*,\s*/', $matches['flags'])
            ];
        }

        return [$element, []];
    }
}
