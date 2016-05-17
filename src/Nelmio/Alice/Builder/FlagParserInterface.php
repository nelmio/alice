<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder;

interface FlagParserInterface
{
    /**
     * Parses a given string to generate a list of comprehensible flags.
     * 
     * @param string $key
     * 
     * @return array
     */
    public function parse(string $key): array;
}
