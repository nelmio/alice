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
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\UniqueFlagParser
 */
class UniqueFlagParserTest extends FlagParserTestCase
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->parser = new UniqueFlagParser();
    }

    public function testIsAChainableFlagParser()
    {
        $this->assertTrue(is_a(UniqueFlagParser::class, ChainableFlagParserInterface::class, true));
    }

    /**
     * @dataProvider provideUniques
     */
    public function testCanParseUniques(string $element, FlagBag $expected = null)
    {
        $this->assertCanParse($element, $expected);
    }
}
