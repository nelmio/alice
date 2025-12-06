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

use InvalidArgumentException;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\GlobalPatternsLexer
 * @internal
 */
final class GlobalPatternsLexerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsALexer(): void
    {
        self::assertTrue(is_a(GlobalPatternsLexer::class, LexerInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(GlobalPatternsLexer::class))->isCloneable());
    }

    public function testLexValueToReturnAToken(): void
    {
        $expected = [
            new Token('10x @users', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE)),
        ];

        $lexer = new GlobalPatternsLexer(new FakeLexer());
        $actual = $lexer->lex('10x @users');

        self::assertCount(count($expected), $actual);
        self::assertEquals($expected, $actual);
    }

    public function testHandOverTheLexificationToTheDecoratedLexerIfNoPatternsMatch(): void
    {
        $value = 'ali%ce';

        $decoratedLexerProphecy = $this->prophesize(LexerInterface::class);
        $decoratedLexerProphecy->lex($value)->willReturn($expected = [new stdClass()]);
        /** @var LexerInterface $decoratedLexer */
        $decoratedLexer = $decoratedLexerProphecy->reveal();

        $lexer = new GlobalPatternsLexer($decoratedLexer);
        $actual = $lexer->lex($value);

        self::assertCount(count($expected), $actual);
        self::assertEquals($expected, $actual);

        $decoratedLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testThrowsAnExceptionWhenInvalidValue(): void
    {
        $lexer = new GlobalPatternsLexer(new FakeLexer());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid token "foo 10x @users" found.');

        $lexer->lex('foo 10x @users');
    }
}
