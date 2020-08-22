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
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\OptionalFlagParser
 */
class OptionalFlagParserTest extends FlagParserTestCase
{
    protected function setUp(): void
    {
        $this->parser = new OptionalFlagParser();
    }

    public function testIsAChainableFlagParser(): void
    {
        static::assertTrue(is_a(OptionalFlagParser::class, ChainableFlagParserInterface::class, true));
    }

    /**
     * @dataProvider provideOptionals
     */
    public function testCanParseOptionals(string $element, FlagBag $expected = null): void
    {
        $this->assertCanParse($element, $expected);
    }
}
