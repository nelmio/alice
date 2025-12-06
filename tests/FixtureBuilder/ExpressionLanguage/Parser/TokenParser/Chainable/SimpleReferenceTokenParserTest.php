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

use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\SimpleReferenceTokenParser
 * @internal
 */
final class SimpleReferenceTokenParserTest extends TestCase
{
    public function testIsAChainableTokenParser(): void
    {
        self::assertTrue(is_a(SimpleReferenceTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(SimpleReferenceTokenParser::class))->isCloneable());
    }

    public function testCanParseDynamicArrayTokens(): void
    {
        $token = new Token('', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new SimpleReferenceTokenParser();

        self::assertTrue($parser->canParse($token));
        self::assertFalse($parser->canParse($anotherToken));
    }

    public function testThrowsAnErrorIfAMalformedTokenIsGiven(): void
    {
        try {
            $token = new Token('', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE));

            $parser = new SimpleReferenceTokenParser();
            $parser->parse($token);
            self::fail('Expected exception to be thrown.');
        } catch (ParseException $exception) {
            self::assertEquals(
                'Could not parse the token "" (type: SIMPLE_REFERENCE_TYPE).',
                $exception->getMessage(),
            );
            self::assertEquals(0, $exception->getCode());
            self::assertNull($exception->getPrevious());
        }
    }

    public function testReturnsAFixtureReferenceValueIfCanParseToken(): void
    {
        $token = new Token('@user', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE));
        $expected = new FixtureReferenceValue('user');

        $parser = new SimpleReferenceTokenParser();
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);
    }
}
