<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Lexer;

use Nelmio\Alice\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\ExpressionLanguage\Lexer\SubPatternsLexer
 */
class SubPatternsLexerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsALexer()
    {
        $this->assertTrue(is_a(SubPatternsLexer::class, LexerInterface::class, true));
    }

    public function testLexReturnsTokens()
    {
        $expected = [
            new Token('<{param}>', new TokenType(TokenType::PARAMETER_TYPE)),
        ];

        $lexer = new SubPatternsLexer(new FakeLexer());
        $actual = $lexer->lex('<{param}>');

        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected, $actual);
    }

    public function testUseReferenceLexerWhenHasReferenceValue()
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

        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected, $actual);

        $referenceLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\ExpressionLanguage\LexException
     * @expectedExceptionMessage Could not lex the value "<{foo".
     */
    public function testThrowLexExceptionWhenCannotLexValue()
    {
        $lexer = new SubPatternsLexer(new FakeLexer());
        $lexer->lex('<{foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid token "<foo>" found.
     */
    public function testThrowExceptionWhenInvalidValue()
    {
        $lexer = new SubPatternsLexer(new FakeLexer());
        $lexer->lex('<foo>');
    }
}
