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
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FunctionLexer
 */
class FunctionLexerTest extends TestCase
{
    /**
     * @var FunctionLexer
     */
    private $lexer;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->lexer = new FunctionLexer(new DummyLexer());
    }

    public function testIsALexer()
    {
        $this->assertTrue(is_a(FunctionLexer::class, LexerInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(FunctionLexer::class))->isCloneable());
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
