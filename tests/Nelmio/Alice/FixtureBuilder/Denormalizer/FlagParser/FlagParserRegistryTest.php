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

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\Loader\NativeLoader;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FlagParserRegistry
 */
class FlagParserRegistryTest extends FlagParserTestCase
{
    public function setUp()
    {
        $parser = (new NativeLoader())->getBuiltInFlagParser();
        $propRefl = (new \ReflectionObject($parser))->getProperty('parser');
        $propRefl->setAccessible(true);

        $this->parser = $propRefl->getValue($parser);
    }

    public function testIsAFlagParser()
    {
        $this->assertTrue(is_a(FlagParserRegistry::class, FlagParserInterface::class, true));
    }

    /**
     * @expectedException \TypeError
     */
    public function testThrowExceptionOnInvalidParserInjected()
    {
        new FlagParserRegistry([new \stdClass()]);
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $parser = new FlagParserRegistry([]);
        clone $parser;
    }

    public function testPicksTheFirstSuitableParser()
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

        $this->assertSame($expected, $actual);
        $parser1Prophecy->canParse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $parser2Prophecy->canParse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $parser2Prophecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserNotFoundException
     * @expectedExceptionMessage No suitable flag parser found to handle the element "string to parse".
     */
    public function testThrowExceptionIfNotSuitableParserFound()
    {
        $parser = new FlagParserRegistry([]);
        $parser->parse('string to parse');
    }

    /**
     * @dataProvider provideExtends
     */
    public function testCanParseExtends(string $element, FlagBag $expected = null)
    {
        $this->assertCanParse($element, $expected);
    }

    /**
     * @dataProvider provideOptionals
     */
    public function testCanParseOptionals(string $element, FlagBag $expected = null)
    {
        $this->assertCanParse($element, $expected);
    }

    /**
     * @dataProvider provideTemplates
     */
    public function testCanParseTemplates(string $element, FlagBag $expected = null)
    {
        $this->assertCanParse($element, $expected);
    }

    /**
     * @dataProvider provideUniques
     */
    public function testCanParseUniques(string $element, FlagBag $expected = null)
    {
        $this->assertCanParse($element, $expected);
    }
}
