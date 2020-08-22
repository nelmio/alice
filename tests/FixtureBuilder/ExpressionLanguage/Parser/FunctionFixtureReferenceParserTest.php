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
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\ListValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FunctionFixtureReferenceParser
 */
class FunctionFixtureReferenceParserTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAParser(): void
    {
        static::assertTrue(is_a(FunctionFixtureReferenceParser::class, ParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(FunctionFixtureReferenceParser::class))->isCloneable());
    }

    public function testIsInstantiatedWithAParser(): void
    {
        new FunctionFixtureReferenceParser(new FakeParser());
    }

    public function testUsesTheDecoratedParserToParseTheGivenValueAndReturnsItsResultIfResultIsNotAListValue(): void
    {
        $value = 'foo';
        $expected = new FakeValue();

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse($value)->willReturn($expected);
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new FunctionFixtureReferenceParser($decoratedParser);
        $actual = $parser->parse($value);

        static::assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testIfTheValueReturnedIsAListValueThenIteratesOverEachValuesToHandleFunctionFixtureReferences(): void
    {
        $value = 'foo';

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy
            ->parse($value)
            ->willReturn(
                new ListValue([
                    new FakeValue(),
                    new FixtureReferenceValue('bob'),
                    new FunctionCallValue('f'),
                    new FakeValue(),
                    new FunctionCallValue('i'),
                    new FixtureReferenceValue('alice'),
                    new FakeValue(),
                    new FixtureReferenceValue('mad'),
                    new FakeValue(),
                    new FunctionCallValue('g'),
                    new FakeValue(),
                    new FixtureReferenceValue('hatter'),
                    new FunctionCallValue('h'),
                ])
            )
        ;
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new ListValue([
            new FakeValue(),
            new FixtureReferenceValue(
                new ListValue([
                    'bob',
                    new FunctionCallValue('f'),
                ])
            ),
            new FakeValue(),
            new FunctionCallValue('i'),
            new FixtureReferenceValue('alice'),
            new FakeValue(),
            new FixtureReferenceValue('mad'),
            new FakeValue(),
            new FunctionCallValue('g'),
            new FakeValue(),
            new FixtureReferenceValue(
                new ListValue([
                    'hatter',
                    new FunctionCallValue('h'),
                ])
            ),
        ]);

        $parser = new FunctionFixtureReferenceParser($decoratedParser);
        $actual = $parser->parse($value);

        static::assertEquals($expected, $actual);

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
                    new FakeValue(),
                ])
            )
        ;
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new ListValue([
            new FakeValue(),
            new FakeValue(),
        ]);

        $parser = new FunctionFixtureReferenceParser($decoratedParser);
        $actual = $parser->parse($value);

        static::assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @dataProvider provideOneElementValues
     */
    public function testIfThereIsOnlyOneElementThenReturnTheElementInsteadOfAValueList($value, $expected): void
    {
        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse(Argument::any())->willReturn($expected);
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new FunctionFixtureReferenceParser($decoratedParser);
        $actual = $parser->parse('');

        static::assertEquals($expected, $actual);
    }

    public function provideOneElementValues()
    {
        yield 'one value' => [
            new FakeValue(),
            new FakeValue(),
        ];

        yield 'a list of one value' => [
            new ListValue([new FakeValue()]),
            new FakeValue(),
        ];

        yield 'a function fixture reference' => [
            new ListValue([
                new FixtureReferenceValue('bob'),
                new FunctionCallValue('foo'),
            ]),
            new FixtureReferenceValue(
                new FunctionCallValue('foo')
            ),
        ];
    }
}
