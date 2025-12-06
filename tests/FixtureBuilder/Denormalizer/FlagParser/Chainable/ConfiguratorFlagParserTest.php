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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(ConfiguratorFlagParser::class)]
final class ConfiguratorFlagParserTest extends FlagParserTestCase
{
    protected function setUp(): void
    {
        $this->parser = new ConfiguratorFlagParser();
    }

    public function testIsAChainableFlagParser(): void
    {
        self::assertTrue(is_a(ConfiguratorFlagParser::class, ChainableFlagParserInterface::class, true));
    }

    #[DataProvider('provideConfigurators')]
    public function testCanParseConfigurators(string $element, ?FlagBag $expected = null): void
    {
        $this->assertCanParse($element, $expected);
    }
}
