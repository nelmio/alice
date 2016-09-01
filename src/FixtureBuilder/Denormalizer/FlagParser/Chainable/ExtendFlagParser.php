<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable;

use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ChainableFlagParserInterface;
use Nelmio\Alice\NotClonableTrait;

final class ExtendFlagParser implements ChainableFlagParserInterface
{
    use NotClonableTrait;

    /** @interval */
    const REGEX = '/^extends (?<reference>.+)$/';

    /**
     * @inheritdoc
     */
    public function canParse(string $element, array &$matches = []): bool
    {
        return 1 === preg_match(self::REGEX, $element, $matches);
    }

    /**
     * @inheritdoc
     */
    public function parse(string $element): FlagBag
    {
        $matches = [];
        $this->canParse($element, $matches);
        $extended = new FixtureReference($matches['reference']);
        
        return (new FlagBag(''))->withFlag(new ExtendFlag($extended));
    }
}
