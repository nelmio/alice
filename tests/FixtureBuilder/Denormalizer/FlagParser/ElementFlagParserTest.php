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

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ElementFlagParser
 */
class ElementFlagParserTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAFlagParser()
    {
        $this->assertTrue(is_a(ElementFlagParser::class, FlagParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new ElementFlagParser(new FakeFlagParser());
    }

    public function testIfNoFlagIsFoundThenReturnsEmptyFlagBag()
    {
        $element = 'dummy _';
        $expected = new FlagBag('dummy _');

        $parser = new ElementFlagParser(new FakeFlagParser());
        $actual = $parser->parse($element);

        $this->assertEquals($expected, $actual);
    }

    public function testIfAFlagIsFoundThenParsesItWithDecoratedParserBeforeReturningTheFlags()
    {
        $element = 'dummy ( flag1 , flag2 )';

        $decoratedParserProphecy = $this->prophesize(FlagParserInterface::class);
        $decoratedParserProphecy
            ->parse('flag1')
            ->willReturn(
                (new FlagBag(''))->with(new ElementFlag('flag1'))
            )
        ;
        $decoratedParserProphecy
            ->parse('flag2')
            ->willReturn(
                (new FlagBag(''))->with(new ElementFlag('flag2'))->with(new ElementFlag('additional flag'))
            )
        ;
        /** @var FlagParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = (new FlagBag('dummy'))
            ->with(new ElementFlag('flag1'))
            ->with(new ElementFlag('flag2'))
            ->with(new ElementFlag('additional flag'))
        ;

        $parser = new ElementFlagParser($decoratedParser);
        $actual = $parser->parse($element);

        $this->assertEquals($expected, $actual);
    }
}
