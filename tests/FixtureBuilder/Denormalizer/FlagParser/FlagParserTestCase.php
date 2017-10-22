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
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

abstract class FlagParserTestCase extends TestCase
{
    /**
     * @var FlagParserInterface|ChainableFlagParserInterface
     */
    protected $parser;

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionObject($this->parser))->isCloneable());
    }

    /**
     * @dataProvider provideElements
     */
    public function testCanParseElements(string $element, FlagBag $expected = null)
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideMalformedElements
     */
    public function testCannotParseMalformedElements(string $element)
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideExtends
     */
    public function testCanParseExtends(string $element, FlagBag $expected = null)
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideMalformedExtends
     */
    public function testCannotParseMalformedExtends(string $element)
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideOptionals
     */
    public function testCanParseOptionals(string $element, FlagBag $expected = null)
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideMalformedOptionals
     */
    public function testCannotParseMalformedOptionals(string $element)
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideTemplates
     */
    public function testCanParseTemplates(string $element, FlagBag $expected = null)
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideUniques
     */
    public function testCanParseUniques(string $element, FlagBag $expected = null)
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideConfigurators
     */
    public function testCanParseConfigurators(string $element, FlagBag $expected = null)
    {
        $this->assertCannotParse($element);
    }

    public function assertCanParse(string $element, FlagBag $expected)
    {
        if ($this->parser instanceof ChainableFlagParserInterface) {
            $this->assertTrue($this->parser->canParse($element));
        }

        $actual = $this->parser->parse($element);
        $this->assertEquals($expected, $actual);
    }

    public function assertCannotParse(string $element)
    {
        if ($this->parser instanceof ChainableFlagParserInterface) {
            $actual = $this->parser->canParse($element);
            $this->assertFalse($actual);

            return;
        }

        try {
            $this->parser->parse($element);
            $this->fail('Expected exception to be thrown.');
        } catch (\LogicException $exception) {
            // expected
        }
    }
    
    public function markAsInvalidCase()
    {
        $this->markTestSkipped('Invalid scenario.');
    }

    public function provideElements()
    {
        return Reference::getElements();
    }

    public function provideMalformedElements()
    {
        return Reference::getMalformedElements();
    }

    public function provideExtends()
    {
        return Reference::getExtends();
    }

    public function provideMalformedExtends()
    {
        return Reference::getMalformedExtends();
    }

    public function provideOptionals()
    {
        return Reference::getOptionals();
    }

    public function provideMalformedOptionals()
    {
        return Reference::getMalformedOptionals();
    }

    public function provideTemplates()
    {
        return Reference::getTemplates();
    }

    public function provideUniques()
    {
        return Reference::getUniques();
    }

    public function provideConfigurators()
    {
        return Reference::getConfigurators();
    }
}
