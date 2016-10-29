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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser;

use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;

interface ChainableFlagParserInterface extends FlagParserInterface
{
    /**
     * Checks if can parse element.
     *
     * @param string $element
     *
     * @return bool
     */
    public function canParse(string $element): bool;
}
