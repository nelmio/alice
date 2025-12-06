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
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FlagParserTestCase;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(ExtendFlagParser::class)]
final class ExtendFlagParserTest extends FlagParserTestCase
{
    protected function setUp(): void
    {
        $this->parser = new ExtendFlagParser();
    }

    public function testIsAChainableFlagParser(): void
    {
        self::assertTrue(is_a(ExtendFlagParser::class, ChainableFlagParserInterface::class, true));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideExtends')]
    public function testCanParseExtends(string $element, ?FlagBag $expected = null): void
    {
        $this->assertCanParse($element, $expected);
    }
}
