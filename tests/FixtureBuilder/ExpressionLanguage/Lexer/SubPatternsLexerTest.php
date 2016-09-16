<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\SubPatternsLexer
 */
class SubPatternsLexerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsALexer()
    {
        $this->assertTrue(is_a(SubPatternsLexer::class, LexerInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new SubPatternsLexer(new FakeLexer());
    }

    public function testLexAValueToReturnAListOfTokens()
    {
        $expected = [
            new Token('<{param}>', new TokenType(TokenType::PARAMETER_TYPE)),
        ];

        $lexer = new SubPatternsLexer(new FakeLexer());
        $actual = $lexer->lex('<{param}>');

        $this->assertEquals(count($expected), count($actual));
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

        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected, $actual);

        $referenceLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\LexException
     * @expectedExceptionMessage Could not lex the value "<{foo".
     */
    public function testThrowsAnExceptionIfCannotLexValue()
    {
        $lexer = new SubPatternsLexer(new FakeLexer());
        $lexer->lex('<{foo');
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
}
