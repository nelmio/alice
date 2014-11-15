<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Util;

class FlagParser
{
    /**
     * parse the given key into a name and flags
     *
     * @param  string $key
     * @return array
     */
    public static function parse($key)
    {
        $flags = [];
        if (preg_match('{^(.+?)\s*\((.+)\)$}', $key, $matches)) {
            foreach (preg_split('{\s*,\s*}', $matches[2]) as $flag) {
                $flags[$flag] = true;
            }
            $key = $matches[1];
        }

        return [$key, $flags];
    }
}
