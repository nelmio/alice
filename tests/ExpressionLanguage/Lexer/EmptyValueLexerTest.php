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

/**
 * @covers Nelmio\Alice\ExpressionLanguage\Lexer\EmptyValueLexer
 */
class EmptyValueLexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmptyValueLexer
     */
    private $lexer;

    public function setUp()
    {
        $this->lexer = new EmptyValueLexer();
    }

    public function testIsALexer()
    {
        $this->assertInstanceOf(LexerInterface::class, $this->lexer);
    }

    public function testLexEmptyStringValue()
    {
        $expected = [
            new Token('', new TokenType(TokenType::STRING_TYPE)),
        ];
        $actual = $this->lexer->lex('');

        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\ExpressionLanguage\LexException
     * @expectedExceptionMessage Could not lex the value "théo".
     */
    public function testCannotLexNonEmptyStringValue()
    {
        $this->lexer->lex('théo');
    }
}
