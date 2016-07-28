<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer;

use Nelmio\Alice\Definition\FlagBag;

interface FlagParserInterface
{
    /**
     * Parses a string element to extract the flags from it.
     *
     * @param string $element
     *
     * @return FlagBag
     */
    public function parse(string $element): FlagBag;
}
