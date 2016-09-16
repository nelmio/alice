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
use Nelmio\Alice\Throwable\ExpressionLanguageParseThrowable;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FunctionLexer
 */
class FunctionLexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FunctionLexer
     */
    private $lexer;

    public function setUp()
    {
        $this->lexer = new FunctionLexer(new DummyLexer());
    }

    public function testIsALexer()
    {
        $this->assertTrue(is_a(FunctionLexer::class, LexerInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new FunctionLexer(new FakeLexer());
    }

    public function testTokenizeValueBeforePassingItToTheDecoratedLexer()
    {
        $value = '<foo()>';

        $decoratedLexerProphecy = $this->prophesize(LexerInterface::class);
        $decoratedLexerProphecy
            ->lex('<aliceTokenizedFunction(FUNCTION_START__foo__IDENTITY_OR_FUNCTION_END)>')
            ->willReturn(
                $expected = [
                    new Token('something', new TokenType(TokenType::FUNCTION_TYPE))
                ]
            )
        ;
        /** @var LexerInterface $decoratedLexer */
        $decoratedLexer = $decoratedLexerProphecy->reveal();

        $lexer = new FunctionLexer($decoratedLexer);
        $actual = $lexer->lex($value);

        $this->assertEquals($expected, $actual);

        $decoratedLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testIfTheValueHasAlreadyBeenTokenizedThenItWillNotBeTokenizedAgain()
    {
        $value = '<aliceTokenizedFunction(something)>';

        $decoratedLexerProphecy = $this->prophesize(LexerInterface::class);
        $decoratedLexerProphecy
            ->lex($value)
            ->willReturn(
                $expected = [
                    new Token('something', new TokenType(TokenType::FUNCTION_TYPE))
                ]
            )
        ;
        /** @var LexerInterface $decoratedLexer */
        $decoratedLexer = $decoratedLexerProphecy->reveal();

        $lexer = new FunctionLexer($decoratedLexer);
        $actual = $lexer->lex($value);

        $this->assertEquals($expected, $actual);

        $decoratedLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
