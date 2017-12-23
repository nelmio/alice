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
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\GlobalPatternsLexer
 */
class GlobalPatternsLexerTest extends TestCase
{
    public function testIsALexer()
    {
        $this->assertTrue(is_a(GlobalPatternsLexer::class, LexerInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(GlobalPatternsLexer::class))->isCloneable());
    }

    public function testLexValueToReturnAToken()
    {
        $expected = [
            new Token('10x @users', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE)),
        ];

        $lexer = new GlobalPatternsLexer(new FakeLexer());
        $actual = $lexer->lex('10x @users');

        $this->assertCount(count($expected), $actual);
        $this->assertEquals($expected, $actual);
    }

    public function testHandOverTheLexificationToTheDecoratedLexerIfNoPatternsMatch()
    {
        $value = 'ali%ce';

        $decoratedLexerProphecy = $this->prophesize(LexerInterface::class);
        $decoratedLexerProphecy->lex($value)->willReturn($expected = [new \stdClass()]);
        /** @var LexerInterface $decoratedLexer */
        $decoratedLexer = $decoratedLexerProphecy->reveal();

        $lexer = new GlobalPatternsLexer($decoratedLexer);
        $actual = $lexer->lex($value);

        $this->assertCount(count($expected), $actual);
        $this->assertEquals($expected, $actual);

        $decoratedLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid token "foo 10x @users" found.
     */
    public function testThrowsAnExceptionWhenInvalidValue()
    {
        $lexer = new GlobalPatternsLexer(new FakeLexer());
        $lexer->lex('foo 10x @users');
    }
}
