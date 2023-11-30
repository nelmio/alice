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

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\StringThenReferenceLexer
 * @internal
 */
class StringThenReferenceLexerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsALexer(): void
    {
        self::assertTrue(is_a(StringThenReferenceLexer::class, LexerInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(StringThenReferenceLexer::class))->isCloneable());
    }

    public function testMergesNonEmptyStringFollowedByAReference(): void
    {
        $value = 'foo55@example.com';
        $expected = [
            new Token('foo', new TokenType(TokenType::STRING_TYPE)),
            new Token('55@example.com', new TokenType(TokenType::STRING_TYPE)),
            new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
        ];

        $decoratedLexerProphecy = $this->prophesize(LexerInterface::class);
        $decoratedLexerProphecy
            ->lex($value)
            ->willReturn(
                [
                    new Token('foo', new TokenType(TokenType::STRING_TYPE)),
                    new Token('55', new TokenType(TokenType::STRING_TYPE)),
                    new Token('@example.com', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE)),
                    new Token(' bar', new TokenType(TokenType::STRING_TYPE)),
                ],
            );
        /** @var LexerInterface $decoratedLexer */
        $decoratedLexer = $decoratedLexerProphecy->reveal();

        $lexer = new StringThenReferenceLexer($decoratedLexer);
        $actual = $lexer->lex($value);

        self::assertCount(count($expected), $actual);
        self::assertEquals($expected, $actual);

        $decoratedLexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
