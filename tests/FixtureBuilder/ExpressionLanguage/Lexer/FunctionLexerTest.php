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
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(FunctionLexer::class)]
final class FunctionLexerTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $lexer = new FunctionLexer(new DummyLexer());
    }

    public function testIsALexer(): void
    {
        self::assertTrue(is_a(FunctionLexer::class, LexerInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(FunctionLexer::class))->isCloneable());
    }

    public function testTokenizeValueBeforePassingItToTheDecoratedLexer(): void
    {
        $value = '<foo()>';

        $decoratedLexerProphecy = $this->prophesize(LexerInterface::class);
        $decoratedLexerProphecy
            ->lex('<aliceTokenizedFunction(FUNCTION_START__foo__IDENTITY_OR_FUNCTION_END)>')
            ->willReturn(
                $expected = [
                    new Token('something', new TokenType(TokenType::FUNCTION_TYPE)),
                ],
            );
        /** @var LexerInterface $decoratedLexer */
        $decoratedLexer = $decoratedLexerProphecy->reveal();

        $lexer = new FunctionLexer($decoratedLexer);
        $actual = $lexer->lex($value);

        self::assertEquals($expected, $actual);

        $decoratedLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testIfTheValueHasAlreadyBeenTokenizedThenItWillNotBeTokenizedAgain(): void
    {
        $value = '<aliceTokenizedFunction(something)>';

        $decoratedLexerProphecy = $this->prophesize(LexerInterface::class);
        $decoratedLexerProphecy
            ->lex($value)
            ->willReturn(
                $expected = [
                    new Token('something', new TokenType(TokenType::FUNCTION_TYPE)),
                ],
            );
        /** @var LexerInterface $decoratedLexer */
        $decoratedLexer = $decoratedLexerProphecy->reveal();

        $lexer = new FunctionLexer($decoratedLexer);
        $actual = $lexer->lex($value);

        self::assertEquals($expected, $actual);

        $decoratedLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
