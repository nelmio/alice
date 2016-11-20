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
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\EmptyValueLexer
 */
class EmptyValueLexerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsALexer()
    {
        $this->assertTrue(is_a(EmptyValueLexer::class, LexerInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotCLonable()
    {
        clone new EmptyValueLexer(new FakeLexer());
    }

    public function testLexEmptyStringIntoAnEmptyStringToken()
    {
        $value = '';
        $expected = [
            new Token('', new TokenType(TokenType::STRING_TYPE)),
        ];

        $lexer = new EmptyValueLexer(new FakeLexer());
        $actual = $lexer->lex('');

        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected, $actual);
    }

    public function testHandOverTheLexificationToItsDecoratedLexerIfStringIsNotEmpty()
    {
        $value = 'bob';

        $decoratedLexerProphecy = $this->prophesize(LexerInterface::class);
        $decoratedLexerProphecy->lex($value)->willReturn($expected = [new \stdClass()]);
        /** @var LexerInterface $decoratedLexer */
        $decoratedLexer = $decoratedLexerProphecy->reveal();

        $lexer = new EmptyValueLexer($decoratedLexer);
        $actual = $lexer->lex($value);

        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected, $actual);

        $decoratedLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
