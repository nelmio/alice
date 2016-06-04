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

use Nelmio\Alice\Fixture\FlagBag;
use Nelmio\Alice\Throwable\BuildThrowable;

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
