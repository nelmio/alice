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

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeFlagParser implements FlagParserInterface
{
    use NotCallableTrait;

    public function parse(string $element): FlagBag
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
