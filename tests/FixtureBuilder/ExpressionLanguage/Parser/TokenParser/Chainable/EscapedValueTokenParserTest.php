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

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 */
#[CoversClass(EscapedValueTokenParser::class)]
final class EscapedValueTokenParserTest extends TestCase
{
    public function testIsAChainableTokenParser(): void
    {
        self::assertTrue(is_a(EscapedValueTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(EscapedValueTokenParser::class))->isCloneable());
    }

    #[DataProvider('provideTokens')]
    public function testCanParseEscapedTokens(Token $token, bool $expected): void
    {
        $parser = new EscapedValueTokenParser();
        $actual = $parser->canParse($token);

        self::assertEquals($expected, $actual);
    }

    public function testThrowsAnExceptionIfAMalformedTokenIsGiven(): void
    {
        $token = new Token('', new TokenType(TokenType::ESCAPED_VALUE_TYPE));

        $parser = new EscapedValueTokenParser();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "" (type: ESCAPED_VALUE_TYPE).');

        $parser->parse($token);
    }

    public function testReturnsEscapedValue(): void
    {
        $token = new Token('\<', new TokenType(TokenType::ESCAPED_VALUE_TYPE));
        $expected = '<';

        $parser = new EscapedValueTokenParser();
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);
    }

    public function testTheEscapedValueIsDetokenizedBeforeBeingReturned(): void
    {
        $token = new Token('\<aliceTokenizedFunction(FUNCTION_START__foo__IDENTITY_OR_FUNCTION_END)>', new TokenType(TokenType::ESCAPED_VALUE_TYPE));
        $expected = '<foo()>';

        $parser = new EscapedValueTokenParser();
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);
    }

    public static function provideTokens(): iterable
    {
        return [
            [
                new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE)),
                false,
            ],
            [
                new Token('', new TokenType(TokenType::ESCAPED_VALUE_TYPE)),
                true,
            ],
        ];
    }
}
