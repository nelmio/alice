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
     * Parses the given key into a name and flags.
     *
     * @param string $key
     *
     * @return array.<string, array> The first element is the parsed key (i.e. the key stripped of the flags) and the
     *                        second element a list of flags, e.g. ['template' => true, 'extends dummy' => true].
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
