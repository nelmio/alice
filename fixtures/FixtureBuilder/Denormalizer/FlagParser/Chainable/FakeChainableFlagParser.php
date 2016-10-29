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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ChainableFlagParserInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeChainableFlagParser implements ChainableFlagParserInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function canParse(string $element): bool
    {
        $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function parse(string $element): FlagBag
    {
        $this->__call(__FUNCTION__, func_get_args());
    }
}
