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

use InvalidArgumentException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @internal
 */
#[CoversClass(SubPatternsLexer::class)]
final class SubPatternsLexerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsALexer(): void
    {
        self::assertTrue(is_a(SubPatternsLexer::class, LexerInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(SubPatternsLexer::class))->isCloneable());
    }

    public function testLexAValueToReturnAListOfTokens(): void
    {
        $expected = [
            new Token('<{param}>', new TokenType(TokenType::PARAMETER_TYPE)),
        ];

        $lexer = new SubPatternsLexer(new FakeLexer());
        $actual = $lexer->lex('<{param}>');

        self::assertCount(count($expected), $actual);
        self::assertEquals($expected, $actual);
    }

    #[DataProvider('lineBreaksProvider')]
    public function testLexAFunctionContainingLineBreaks(string $lineBreak): void
    {
        $expected = [
            new Token('<identity("foo'.$lineBreak.'bar")>', new TokenType(TokenType::FUNCTION_TYPE)),
        ];

        $lexer = new SubPatternsLexer(new FakeLexer());
        $actual = $lexer->lex('<identity("foo'.$lineBreak.'bar")>');

        self::assertCount(count($expected), $actual);
        self::assertEquals($expected, $actual);
    }

    public function testUsesDecoratedLexerToLexReferenceValues(): void
    {
        $value = '@user';
        $expected = [
            new Token('@user', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
        ];

        $referenceLexerProphecy = $this->prophesize(LexerInterface::class);
        $referenceLexerProphecy->lex('@user')->willReturn($expected);
        /** @var LexerInterface $referenceLexer */
        $referenceLexer = $referenceLexerProphecy->reveal();

        $lexer = new SubPatternsLexer($referenceLexer);
        $actual = $lexer->lex($value);

        self::assertCount(count($expected), $actual);
        self::assertEquals($expected, $actual);

        $referenceLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testReturnsAStringTokenIfCannotLexValue(): void
    {
        $value = '<{foo';
        $expected = [
            new Token('<{foo', new TokenType(TokenType::STRING_TYPE)),
        ];

        $lexer = new SubPatternsLexer(new FakeLexer());
        $actual = $lexer->lex($value);

        self::assertCount(count($expected), $actual);
        self::assertEquals($expected, $actual);
    }

    public function testThrowsAnExceptionWhenAnInvalidValueIsGiven(): void
    {
        $lexer = new SubPatternsLexer(new FakeLexer());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid token "<foo>" found.');

        $lexer->lex('<foo>');
    }

    public static function lineBreaksProvider(): iterable
    {
        return [
            ['\n'],
            ['\r\n'],
        ];
    }
}
