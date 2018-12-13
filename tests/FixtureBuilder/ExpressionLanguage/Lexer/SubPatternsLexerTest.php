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
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\SubPatternsLexer
 */
class SubPatternsLexerTest extends TestCase
{
    public function testIsALexer()
    {
        $this->assertTrue(is_a(SubPatternsLexer::class, LexerInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(SubPatternsLexer::class))->isCloneable());
    }

    public function testLexAValueToReturnAListOfTokens()
    {
        $expected = [
            new Token('<{param}>', new TokenType(TokenType::PARAMETER_TYPE)),
        ];

        $lexer = new SubPatternsLexer(new FakeLexer());
        $actual = $lexer->lex('<{param}>');

        $this->assertCount(count($expected), $actual);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider lineBreaksProvider
     */
    public function testLexAFunctionContainingLineBreaks(string $lineBreak)
    {
        $expected = [
            new Token('<identity("foo'.$lineBreak.'bar")>', new TokenType(TokenType::FUNCTION_TYPE)),
        ];

        $lexer = new SubPatternsLexer(new FakeLexer());
        $actual = $lexer->lex('<identity("foo'.$lineBreak.'bar")>');

        $this->assertCount(count($expected), $actual);
        $this->assertEquals($expected, $actual);
    }

    public function testUsesDecoratedLexerToLexReferenceValues()
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

        $this->assertCount(count($expected), $actual);
        $this->assertEquals($expected, $actual);

        $referenceLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testReturnsAStringTokenIfCannotLexValue()
    {
        $value = '<{foo';
        $expected = [
            new Token('<{foo', new TokenType(TokenType::STRING_TYPE)),
        ];

        $lexer = new SubPatternsLexer(new FakeLexer());
        $actual = $lexer->lex($value);

        $this->assertCount(count($expected), $actual);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid token "<foo>" found.
     */
    public function testThrowsAnExceptionWhenAnInvalidValueIsGiven()
    {
        $lexer = new SubPatternsLexer(new FakeLexer());
        $lexer->lex('<foo>');
    }

    public function lineBreaksProvider()
    {
        return [
            ['\n'],
            ['\r\n'],
        ];
    }
}
