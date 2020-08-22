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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer;

use Nelmio\Alice\Definition\FlagBag;

interface FlagParserInterface
{
    /**
     * Parses a string element to extract the flags from it.
     *
     * @param string $element e.g. 'user0 (dummy_flag, another_flag)'
     */
    public function parse(string $element): FlagBag;
}
