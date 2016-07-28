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

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\ElementFlagParser
 */
class ElementFlagParserTest extends FlagParserTestCase
{
    /**
     * @var ElementFlagParser
     */
    private $elementParser;

    public function setUp()
    {
        $this->parser = (new NativeLoader())->getBuiltInFlagParser();
        $this->elementParser = new ElementFlagParser(new ElementParser());
    }

    public function testIsAFlagParser()
    {
        $this->assertTrue(is_a(ElementFlagParser::class, FlagParserInterface::class, true));
    }

    /**
     * @dataProvider provideElements
     */
    public function testCanParseElements(string $element, FlagBag $expected = null)
    {
        $actual = $this->elementParser->parse($element);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideMalformedElements
     */
    public function testCannotParseMalformedElements(string $element)
    {
        try {
            $this->elementParser->parse($element);
            $this->fail('Expected exception to be thrown.');
        } catch (\RuntimeException $exception) {
            // expected
        }
    }

    public function assertCanParse(string $element, FlagBag $expected)
    {
        $actual = $this->parser->parse($element);
        $this->assertEquals($expected, $actual);
    }

    public function assertCannotParse(string $element)
    {
        try {
            $this->parser->parse($element);
            $this->fail('Expected exception to be thrown.');
        } catch (\RuntimeException $exception) {
            // expected
        }
    }
}
