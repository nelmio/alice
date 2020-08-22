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
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\EmptyValueLexer
 */
class EmptyValueLexerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsALexer(): void
    {
        static::assertTrue(is_a(EmptyValueLexer::class, LexerInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(EmptyValueLexer::class))->isCloneable());
    }

    public function testLexEmptyStringIntoAnEmptyStringToken(): void
    {
        $value = '';
        $expected = [
            new Token('', new TokenType(TokenType::STRING_TYPE)),
        ];

        $lexer = new EmptyValueLexer(new FakeLexer());
        $actual = $lexer->lex('');

        static::assertCount(count($expected), $actual);
        static::assertEquals($expected, $actual);
    }

    public function testHandOverTheLexificationToItsDecoratedLexerIfStringIsNotEmpty(): void
    {
        $value = 'bob';

        $decoratedLexerProphecy = $this->prophesize(LexerInterface::class);
        $decoratedLexerProphecy->lex($value)->willReturn($expected = [new stdClass()]);
        /** @var LexerInterface $decoratedLexer */
        $decoratedLexer = $decoratedLexerProphecy->reveal();

        $lexer = new EmptyValueLexer($decoratedLexer);
        $actual = $lexer->lex($value);

        static::assertCount(count($expected), $actual);
        static::assertEquals($expected, $actual);

        $decoratedLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
