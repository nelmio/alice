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

use LogicException;
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

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionObject($this->parser))->isCloneable());
    }

    /**
     * @dataProvider provideElements
     */
    public function testCanParseElements(string $element, ?FlagBag $expected = null): void
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideMalformedElements
     */
    public function testCannotParseMalformedElements(string $element): void
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideExtends
     */
    public function testCanParseExtends(string $element, ?FlagBag $expected = null): void
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideMalformedExtends
     */
    public function testCannotParseMalformedExtends(string $element): void
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideOptionals
     */
    public function testCanParseOptionals(string $element, ?FlagBag $expected = null): void
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideMalformedOptionals
     */
    public function testCannotParseMalformedOptionals(string $element): void
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideTemplates
     */
    public function testCanParseTemplates(string $element, ?FlagBag $expected = null): void
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideUniques
     */
    public function testCanParseUniques(string $element, ?FlagBag $expected = null): void
    {
        $this->assertCannotParse($element);
    }

    /**
     * @dataProvider provideConfigurators
     */
    public function testCanParseConfigurators(string $element, ?FlagBag $expected = null): void
    {
        $this->assertCannotParse($element);
    }

    public function assertCanParse(string $element, FlagBag $expected): void
    {
        if ($this->parser instanceof ChainableFlagParserInterface) {
            self::assertTrue($this->parser->canParse($element));
        }

        $actual = $this->parser->parse($element);
        self::assertEquals($expected, $actual);
    }

    public function assertCannotParse(string $element): void
    {
        if ($this->parser instanceof ChainableFlagParserInterface) {
            $actual = $this->parser->canParse($element);
            self::assertFalse($actual);

            return;
        }

        try {
            $this->parser->parse($element);
            self::fail('Expected exception to be thrown.');
        } catch (LogicException $exception) {
            // expected
        }
    }

    public function markAsInvalidCase(): void
    {
        self::markTestSkipped('Invalid scenario.');
    }

    public function provideElements(): iterable
    {
        return Reference::getElements();
    }

    public function provideMalformedElements(): iterable
    {
        return Reference::getMalformedElements();
    }

    public function provideExtends(): iterable
    {
        return Reference::getExtends();
    }

    public function provideMalformedExtends(): iterable
    {
        return Reference::getMalformedExtends();
    }

    public function provideOptionals(): iterable
    {
        return Reference::getOptionals();
    }

    public function provideMalformedOptionals(): iterable
    {
        return Reference::getMalformedOptionals();
    }

    public function provideTemplates(): iterable
    {
        return Reference::getTemplates();
    }

    public function provideUniques(): iterable
    {
        return Reference::getUniques();
    }

    public function provideConfigurators(): iterable
    {
        return Reference::getConfigurators();
    }
}
