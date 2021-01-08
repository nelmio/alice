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

use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ChainableFlagParserInterface;
use Nelmio\Alice\IsAServiceTrait;

final class ExtendFlagParser implements ChainableFlagParserInterface
{
    use IsAServiceTrait;

    /** @interval */
    const REGEX = '/^extends (?<reference>.+)$/';
    
    public function canParse(string $element, array &$matches = []): bool
    {
        return 1 === preg_match(self::REGEX, $element, $matches);
    }
    
    public function parse(string $element): FlagBag
    {
        $matches = [];
        $this->canParse($element, $matches);
        $extended = new FixtureReference($matches['reference']);
        
        return (new FlagBag(''))->withFlag(new ExtendFlag($extended));
    }
}
