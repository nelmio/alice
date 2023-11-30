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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceEscaperLexer
 * @internal
 */
class ReferenceEscaperLexerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsALexer(): void
    {
        self::assertTrue(is_a(ReferenceEscaperLexer::class, LexerInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(ReferenceEscaperLexer::class))->isCloneable());
    }

    /**
     * @dataProvider provideValues
     */
    public function testEscapesStringBeforeHandlingItOverToTheDecoratedLexer(string $value, ?string $expectedEscapedValue = null): void
    {
        if (null === $expectedEscapedValue) {
            $expectedEscapedValue = $value;
        }

        $decoratedLexerProphecy = $this->prophesize(LexerInterface::class);
        $decoratedLexerProphecy->lex($expectedEscapedValue)->willReturn($expected = [new stdClass()]);
        /** @var LexerInterface $decoratedLexer */
        $decoratedLexer = $decoratedLexerProphecy->reveal();

        $lexer = new ReferenceEscaperLexer($decoratedLexer);
        $actual = $lexer->lex($value);

        self::assertEquals($expected, $actual);

        $decoratedLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function provideValues(): iterable
    {
        yield 'empty string' => [''];

        yield 'regular string' => ['hello world'];

        yield 'string with a reference' => ['@foo'];

        yield 'string with a reference with members' => ['bar @foo baz'];

        yield 'reference in a middle of a word' => ['email@example', 'email\\@example'];
    }
}
