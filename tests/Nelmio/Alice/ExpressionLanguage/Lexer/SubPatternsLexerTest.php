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
}
