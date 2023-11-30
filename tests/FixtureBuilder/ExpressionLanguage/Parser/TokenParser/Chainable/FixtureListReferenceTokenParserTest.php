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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\Definition\Value\ArrayValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureListReferenceTokenParser
 * @internal
 */
class FixtureListReferenceTokenParserTest extends TestCase
{
    public function testIsAChainableTokenParser(): void
    {
        self::assertTrue(is_a(FixtureListReferenceTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(FixtureListReferenceTokenParser::class))->isCloneable());
    }

    /**
     * @dataProvider provideTokens
     */
    public function testCanParseListReferenceTokens(Token $token, bool $expected): void
    {
        $parser = new FixtureListReferenceTokenParser();
        $actual = $parser->canParse($token);

        self::assertEquals($expected, $actual);
    }

    public function testThrowsAnExceptionIfInvalidTokenIsGiven(): void
    {
        $token = new Token('', new TokenType(TokenType::LIST_REFERENCE_TYPE));

        $parser = new FixtureListReferenceTokenParser();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "" (type: LIST_REFERENCE_TYPE).');

        $parser->parse($token);
    }

    public function testThrowsAnExceptionIfAMalformedTokenIsGiven(): void
    {
        $token = new Token('', new TokenType(TokenType::LIST_REFERENCE_TYPE));

        $parser = new FixtureListReferenceTokenParser();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "" (type: LIST_REFERENCE_TYPE).');

        $parser->parse($token);
    }

    public function testReturnsListOfPossibleValues(): void
    {
        $token = new Token('@user_{alice, bob}', new TokenType(TokenType::LIST_REFERENCE_TYPE));
        $expected = new ArrayValue([
            new FixtureReferenceValue('user_alice'),
            new FixtureReferenceValue('user_bob'),
        ]);

        $parser = new FixtureListReferenceTokenParser();
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);
    }

    public function provideTokens(): iterable
    {
        return [
            [
                new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE)),
                false,
            ],
            [
                new Token('', new TokenType(TokenType::LIST_REFERENCE_TYPE)),
                true,
            ],
        ];
    }
}
