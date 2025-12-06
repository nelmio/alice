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
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureRangeReferenceTokenParser
 * @internal
 */
final class FixtureRangeReferenceTokenParserTest extends TestCase
{
    public function testIsAChainableTokenParser(): void
    {
        self::assertTrue(is_a(FixtureRangeReferenceTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(FixtureRangeReferenceTokenParser::class))->isCloneable());
    }

    public function testCanParseRangedReferencesTokens(): void
    {
        $token = new Token('', new TokenType(TokenType::RANGE_REFERENCE_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new FixtureRangeReferenceTokenParser();

        self::assertTrue($parser->canParse($token));
        self::assertFalse($parser->canParse($anotherToken));
    }

    public function testThrowsAnExceptionIfPassedTokenIsMalformed(): void
    {
        $token = new Token('', new TokenType(TokenType::RANGE_REFERENCE_TYPE));
        $parser = new FixtureRangeReferenceTokenParser();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "" (type: RANGE_REFERENCE_TYPE).');

        $parser->parse($token);
    }

    public function testThrowsAnExceptionIfPassedTokenIsInvalid(): void
    {
        $token = new Token('@user{1..10', new TokenType(TokenType::RANGE_REFERENCE_TYPE));
        $parser = new FixtureRangeReferenceTokenParser();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "@user{1..10" (type: RANGE_REFERENCE_TYPE).');

        $parser->parse($token);
    }

    public function testThrowsAnExceptionIfAMalformedTokenIsGiven(): void
    {
        $token = new Token('', new TokenType(TokenType::RANGE_REFERENCE_TYPE));

        $parser = new FixtureListReferenceTokenParser();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "" (type: RANGE_REFERENCE_TYPE).');

        $parser->parse($token);
    }

    public function testReturnsAChoiceListIfCanParseToken(): void
    {
        $token = new Token('@user{10..8}', new TokenType(TokenType::RANGE_REFERENCE_TYPE));
        $expected = new ArrayValue([
            new FixtureReferenceValue('user8'),
            new FixtureReferenceValue('user9'),
            new FixtureReferenceValue('user10'),
        ]);

        $parser = new FixtureRangeReferenceTokenParser();
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);
    }

    public function testReturnsAChoiceListWithStepsIfCanParseToken(): void
    {
        $token = new Token('@user{1..5, 2}', new TokenType(TokenType::RANGE_REFERENCE_TYPE));
        $expected = new ArrayValue([
            new FixtureReferenceValue('user1'),
            new FixtureReferenceValue('user3'),
            new FixtureReferenceValue('user5'),
        ]);

        $parser = new FixtureRangeReferenceTokenParser();
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);
    }
}
