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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser;

use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\ListValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\StringMergerParser
 * @internal
 */
class StringMergerParserTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAParser(): void
    {
        self::assertTrue(is_a(StringMergerParser::class, ParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(StringMergerParser::class))->isCloneable());
    }

    public function testIsInstantiatedWithAParser(): void
    {
        new StringMergerParser(new FakeParser());
    }

    public function testUsesTheDecoratedParserToParseTheGivenValueAndReturnsItsResultIfResultIsNotAListValue(): void
    {
        $value = 'foo';
        $expected = new FakeValue();

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse($value)->willReturn($expected);
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new StringMergerParser($decoratedParser);
        $actual = $parser->parse($value);

        self::assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testIfTheValueReturnedIsAListValueThenIteratesOverEachValuesToMergeStrings(): void
    {
        $value = 'foo';

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy
            ->parse($value)
            ->willReturn(
                new ListValue([
                    new FakeValue(),
                    'az',
                    'er',
                    'ty',
                    new FakeValue(),
                    'qw',
                    new FakeValue(),
                    'er',
                    'ty',
                ]),
            );
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new ListValue([
            new FakeValue(),
            'azerty',
            new FakeValue(),
            'qw',
            new FakeValue(),
            'erty',
        ]);

        $parser = new StringMergerParser($decoratedParser);
        $actual = $parser->parse($value);

        self::assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testIfNotFunctionFixtureReferenceIsFoundThenTheResultWillRemainUnchanged(): void
    {
        $value = 'foo';

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy
            ->parse($value)
            ->willReturn(
                new ListValue([
                    new FakeValue(),
                    'azerty',
                    new FakeValue(),
                    'qw',
                    new FakeValue(),
                    'erty',
                ]),
            );
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new ListValue([
            new FakeValue(),
            'azerty',
            new FakeValue(),
            'qw',
            new FakeValue(),
            'erty',
        ]);

        $parser = new StringMergerParser($decoratedParser);
        $actual = $parser->parse($value);

        self::assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @dataProvider provideOneElementValues
     * @param mixed $parsedValue
     * @param mixed $expected
     */
    public function testIfThereIsOnlyOneElementThenReturnTheElementInsteadOfAValueList($parsedValue, $expected): void
    {
        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse(Argument::any())->willReturn($parsedValue);
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new StringMergerParser($decoratedParser);
        $actual = $parser->parse('');

        self::assertEquals($expected, $actual);
    }

    public static function provideOneElementValues(): iterable
    {
        yield 'one value' => [
            new FakeValue(),
            new FakeValue(),
        ];

        yield 'one string value' => [
            'foo',
            'foo',
        ];

        yield 'a list of one value' => [
            new ListValue([new FakeValue()]),
            new FakeValue(),
        ];

        yield 'a function fixture reference' => [
            new ListValue([
                'azer',
                'ty',
            ]),
            'azerty',
        ];
    }
}
