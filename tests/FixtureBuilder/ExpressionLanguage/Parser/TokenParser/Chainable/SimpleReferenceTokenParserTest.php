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
 */
class SimpleReferenceTokenParserTest extends TestCase
{
    public function testIsAChainableTokenParser(): void
    {
        static::assertTrue(is_a(SimpleReferenceTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(SimpleReferenceTokenParser::class))->isCloneable());
    }

    public function testCanParseDynamicArrayTokens(): void
    {
        $token = new Token('', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new SimpleReferenceTokenParser();

        static::assertTrue($parser->canParse($token));
        static::assertFalse($parser->canParse($anotherToken));
    }

    public function testThrowsAnErrorIfAMalformedTokenIsGiven(): void
    {
        try {
            $token = new Token('', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE));

            $parser = new SimpleReferenceTokenParser();
            $parser->parse($token);
            static::fail('Expected exception to be thrown.');
        } catch (ParseException $exception) {
            static::assertEquals(
                'Could not parse the token "" (type: SIMPLE_REFERENCE_TYPE).',
                $exception->getMessage()
            );
            static::assertEquals(0, $exception->getCode());
            static::assertNull($exception->getPrevious());
        }
    }

    public function testReturnsAFixtureReferenceValueIfCanParseToken(): void
    {
        $token = new Token('@user', new TokenType(TokenType::SIMPLE_REFERENCE_TYPE));
        $expected = new FixtureReferenceValue('user');

        $parser = new SimpleReferenceTokenParser();
        $actual = $parser->parse($token);

        static::assertEquals($expected, $actual);
    }
}
