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

use Nelmio\Alice\Definition\Value\ParameterValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\ParameterTokenParser
 */
class ParameterTokenParserTest extends TestCase
{
    public function testIsAChainableTokenParser(): void
    {
        static::assertTrue(is_a(ParameterTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(ParameterTokenParser::class))->isCloneable());
    }

    public function testCanParseMethodTokens(): void
    {
        $token = new Token('', new TokenType(TokenType::PARAMETER_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new ParameterTokenParser();

        static::assertTrue($parser->canParse($token));
        static::assertFalse($parser->canParse($anotherToken));
    }

    public function testThrowsAnErrorIfPassedParameterIsMalformed(): void
    {
        try {
            $token = new Token('', new TokenType(TokenType::PARAMETER_TYPE));
            $parser = new ParameterTokenParser();

            $parser->parse($token);
            static::fail('Expected exception to be thrown.');
        } catch (ParseException $exception) {
            static::assertEquals(
                'Could not parse the token "" (type: PARAMETER_TYPE).',
                $exception->getMessage()
            );
            static::assertEquals(0, $exception->getCode());
            static::assertNotNull($exception->getPrevious());
        }
    }

    public function testReturnsAParameterValueIfCanParseToken(): void
    {
        $token = new Token('<{param}>', new TokenType(TokenType::PARAMETER_TYPE));
        $expected = new ParameterValue('param');

        $parser = new ParameterTokenParser();
        $actual = $parser->parse($token);

        static::assertEquals($expected, $actual);
    }
}
