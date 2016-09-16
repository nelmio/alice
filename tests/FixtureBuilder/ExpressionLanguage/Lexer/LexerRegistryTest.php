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

use Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\LexException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\NotClonableTrait;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\LexerRegistry
 */
class LexerRegistryTest extends \PHPUnit_Framework_TestCase
{
    use NotClonableTrait;

    public function testIsALexer()
    {
        $this->assertTrue(is_a(LexerRegistry::class, LexerInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new LexerRegistry([]);
    }

    public function takesLexers()
    {
        new LexerRegistry([]);
        new LexerRegistry([new FakeLexer()]);
    }

    public function testPicksTheFirstSuitableLexerToLexerTheGivenValue()
    {
        $value = 'random';
        $expected = [new Token('random', new TokenType(TokenType::STRING_TYPE))];

        $lexer1Prophecy = $this->prophesize(LexerInterface::class);
        $lexer1Prophecy->lex($value)->willThrow(LexException::class);
        /** @var LexerInterface $lexer1 */
        $lexer1 = $lexer1Prophecy->reveal();

        $lexer2Prophecy = $this->prophesize(LexerInterface::class);
        $lexer2Prophecy->lex($value)->willReturn($expected);
        /** @var LexerInterface $lexer2 */
        $lexer2 = $lexer2Prophecy->reveal();

        $lexer3Prophecy = $this->prophesize(LexerInterface::class);
        $lexer3Prophecy->lex(Argument::any())->shouldNotBeCalled();
        /** @var LexerInterface $lexer3 */
        $lexer3 = $lexer3Prophecy->reveal();

        $lexer = new LexerRegistry([$lexer1, $lexer2, $lexer3]);
        $actual = $lexer->lex($value);

        $this->assertSame($expected, $actual);

        $lexer1Prophecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
        $lexer2Prophecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\LexException
     * @expectedExceptionMessage Could not lex the value "".
     */
    public function testThrowsAnExceptionIfNoLexerCanLexTheValue()
    {
        $lexer = new LexerRegistry([]);
        $lexer->lex('');
    }
}
