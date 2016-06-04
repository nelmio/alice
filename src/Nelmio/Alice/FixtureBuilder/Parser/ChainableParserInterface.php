<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Parser;

use Nelmio\Alice\FixtureBuilder\ParserInterface;

interface ChainableParserInterface extends ParserInterface
{
    /**
     * @param string $file File path
     *
     * @return bool
     */
    public function canParse(string $file): bool;
}
