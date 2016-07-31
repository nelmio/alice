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

/**
 * @covers Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\GlobalPatternsLexer
 */
class GlobalPatternsLexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GlobalPatternsLexer
     */
    private $lexer;

    public function setUp()
    {
        $this->lexer = new GlobalPatternsLexer();
    }

    public function testIsALexer()
    {
        $this->assertInstanceOf(LexerInterface::class, $this->lexer);
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone $this->lexer;
    }

    public function testLexValueToReturnAToken()
    {
        $expected = [
            new Token('10x @users', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE)),
        ];
        $actual = $this->lexer->lex('10x @users');

        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\LexException
     * @expectedExceptionMessage Could not lex the value "th%éo".
     */
    public function testThrowsAnExceptionIfCannotLexValue()
    {
        $this->lexer->lex('th%éo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid token "foo 10x @users" found.
     */
    public function testThrowsAnExceptionWhenInvalidValue()
    {
        $this->lexer->lex('foo 10x @users');
    }
}
