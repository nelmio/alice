<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser;

use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;

final class ElementFlagParser implements FlagParserInterface
{
    /**
     * @inheritdoc
     */
    public function parse(string $element): FlagBag
    {
        return (new FlagBag($element))->with(new ElementFlag($element));
    }
}
