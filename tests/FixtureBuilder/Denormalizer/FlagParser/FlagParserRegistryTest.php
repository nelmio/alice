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
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\Chainable\FakeChainableFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;
use TypeError;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FlagParserRegistry
 * @internal
 */
class FlagParserRegistryTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAFlagParser(): void
    {
        self::assertTrue(is_a(FlagParserRegistry::class, FlagParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(FlagParserRegistry::class))->isCloneable());
    }

    public function testThrowsAnExceptionIfAnInvalidParserInjected(): void
    {
        $this->expectException(TypeError::class);

        new FlagParserRegistry([new stdClass()]);
    }

    public function testCanBeInstantiatedWithChainableParsers(): void
    {
        new FlagParserRegistry([new FakeChainableFlagParser()]);
    }

    public function testPicksTheFirstSuitableParser(): void
    {
        $element = 'string to parse';
        $expected = new FlagBag('');

        $parser1Prophecy = $this->prophesize(ChainableFlagParserInterface::class);
        $parser1Prophecy->canParse($element)->willReturn(false);
        /** @var ChainableFlagParserInterface $parser1 */
        $parser1 = $parser1Prophecy->reveal();

        $parser2Prophecy = $this->prophesize(ChainableFlagParserInterface::class);
        $parser2Prophecy->canParse($element)->willReturn(true);
        $parser2Prophecy->parse($element)->willReturn($expected);
        /** @var ChainableFlagParserInterface $parser2 */
        $parser2 = $parser2Prophecy->reveal();

        $parser3Prophecy = $this->prophesize(ChainableFlagParserInterface::class);
        $parser3Prophecy->canParse(Argument::any())->shouldNotBeCalled();
        /** @var ChainableFlagParserInterface $parser3 */
        $parser3 = $parser3Prophecy->reveal();

        $parser = new FlagParserRegistry([$parser1, $parser2, $parser3]);
        $actual = $parser->parse($element);

        self::assertSame($expected, $actual);
        $parser1Prophecy->canParse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $parser2Prophecy->canParse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $parser2Prophecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testThrowsAnExceptionIfNotSuitableParserFound(): void
    {
        $parser = new FlagParserRegistry([]);

        $this->expectException(FlagParserNotFoundException::class);
        $this->expectExceptionMessage('No suitable flag parser found to handle the element "string to parse".');

        $parser->parse('string to parse');
    }
}
