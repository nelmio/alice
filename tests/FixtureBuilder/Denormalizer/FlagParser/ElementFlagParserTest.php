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

use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ElementFlagParser
 */
class ElementFlagParserTest extends FlagParserTestCase
{
    use ProphecyTrait;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->parser = new ElementFlagParser(new ElementParser());
    }

    public function testIsAFlagParser(): void
    {
        static::assertTrue(is_a(ElementFlagParser::class, FlagParserInterface::class, true));
    }

    public function testIfNoFlagIsFoundThenReturnsEmptyFlagBag(): void
    {
        $element = 'dummy _';
        $expected = new FlagBag('dummy _');

        $parser = new ElementFlagParser(new FakeFlagParser());
        $actual = $parser->parse($element);

        static::assertEquals($expected, $actual);
    }

    public function testIfAFlagIsFoundThenParsesItWithDecoratedParserBeforeReturningTheFlags(): void
    {
        $element = 'dummy ( flag1 , flag2 )';

        $decoratedParserProphecy = $this->prophesize(FlagParserInterface::class);
        $decoratedParserProphecy
            ->parse('flag1')
            ->willReturn(
                (new FlagBag(''))->withFlag(new ElementFlag('flag1'))
            )
        ;
        $decoratedParserProphecy
            ->parse('flag2')
            ->willReturn(
                (new FlagBag(''))->withFlag(new ElementFlag('flag2'))->withFlag(new ElementFlag('additional flag'))
            )
        ;
        /** @var FlagParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = (new FlagBag('dummy'))
            ->withFlag(new ElementFlag('flag1'))
            ->withFlag(new ElementFlag('flag2'))
            ->withFlag(new ElementFlag('additional flag'))
        ;

        $parser = new ElementFlagParser($decoratedParser);
        $actual = $parser->parse($element);

        static::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideElements
     */
    public function testCanParseElements(string $element, FlagBag $expected = null): void
    {
        $actual = $this->parser->parse($element);

        static::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideMalformedElements
     */
    public function testCannotParseMalformedElements(string $element): void
    {
        try {
            $this->parser->parse($element);
            static::fail('Expected exception to be thrown.');
        } catch (RuntimeException $exception) {
            // expected
        }
    }

    public function assertCanParse(string $element, FlagBag $expected): void
    {
        // Do nothing: skip those tests as are irrelevant for this parser
    }

    public function assertCannotParse(string $element): void
    {
        // Do nothing: skip those tests as are irrelevant for this parser
    }
}
