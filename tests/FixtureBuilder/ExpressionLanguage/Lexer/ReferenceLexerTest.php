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
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\LexException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\ReferenceLexer
 */
class ReferenceLexerTest extends TestCase
{
    /**
     * @var ReferenceLexer
     */
    private $lexer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->lexer = new ReferenceLexer();
    }

    public function testIsALexer()
    {
        $this->assertInstanceOf(LexerInterface::class, $this->lexer);
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ReferenceLexer::class))->isCloneable());
    }

    public function testThrowsAnExceptionWhenAnInvalidValueIsGiven()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid token "@u->" found.');

        $this->lexer->lex('@u->');
    }

    /**
     * @dataProvider provideValues
     */
    public function testReturnsMatchingToken(string $value, array $expected)
    {
        $actual = $this->lexer->lex($value);

        $this->assertEquals($expected, $actual);
    }

    public function testUsesTheRegexCachedGroupForTheTokenValue()
    {
        $value = '@user foo';
        $expected = [new Token('@user', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE))];

        $actual = $this->lexer->lex($value);

        $this->assertEquals($expected, $actual);
    }

    public function testThrowsAnExceptionIfNoMatchingPatternFound()
    {
        $this->expectException(LexException::class);
        $this->expectExceptionMessage('Could not lex the value "foo".');

        $this->lexer->lex('foo');
    }

    public function provideValues()
    {
        yield 'method reference' => [
            $value = '@user->getUserName()',
            [new Token($value, new TokenType(TokenType::METHOD_REFERENCE_TYPE))],
        ];

        yield 'property reference' => [
            $value = '@user->username',
            [new Token($value, new TokenType(TokenType::PROPERTY_REFERENCE_TYPE))],
        ];

        yield 'reference' => [
            $value = '@user{1..2}',
            [new Token($value, new TokenType(TokenType::RANGE_REFERENCE_TYPE))],
        ];

        yield 'list reference' => [
            $value = '@user_{alice, bob}',
            [new Token($value, new TokenType(TokenType::LIST_REFERENCE_TYPE))],
        ];

        yield 'wildcard reference' => [
            $value = '@user*',
            [new Token($value, new TokenType(TokenType::WILDCARD_REFERENCE_TYPE))],
        ];

        yield 'simple reference' => [
            $value = '@user',
            [new Token($value, new TokenType(TokenType::SIMPLE_REFERENCE_TYPE))],
        ];

        yield 'simple empty reference' => [
            $value = '@',
            [new Token($value, new TokenType(TokenType::SIMPLE_REFERENCE_TYPE))],
        ];

        yield 'simple reference with second member' => [
            $value = '@ user',
            [new Token('@', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE))],
        ];

        yield 'property reference with interpolation' => [
            $value = '@user_<current()>->email',
            [new Token($value, new TokenType(TokenType::PROPERTY_REFERENCE_TYPE))],
        ];
    }
}
