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

use Nelmio\Alice\Definition\Value\VariableValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\VariableTokenParser
 * @internal
 */
class VariableTokenParserTest extends TestCase
{
    public function testIsAChainableTokenParser(): void
    {
        self::assertTrue(is_a(VariableTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(VariableTokenParser::class))->isCloneable());
    }

    public function testCanParseDynamicArrayTokens(): void
    {
        $token = new Token('', new TokenType(TokenType::VARIABLE_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new VariableTokenParser();

        self::assertTrue($parser->canParse($token));
        self::assertFalse($parser->canParse($anotherToken));
    }

    public function testThrowsAnExceptionIfCouldNotParseToken(): void
    {
        try {
            $token = new Token('', new TokenType(TokenType::VARIABLE_TYPE));
            $parser = new VariableTokenParser();

            $parser->parse($token);
            self::fail('Expected exception to be thrown.');
        } catch (ParseException $exception) {
            self::assertEquals(
                'Could not parse the token "" (type: VARIABLE_TYPE).',
                $exception->getMessage(),
            );
            self::assertEquals(0, $exception->getCode());
            self::assertNotNull($exception->getPrevious());
        }
    }

    public function testReturnsADynamicArrayIfCanParseToken(): void
    {
        $token = new Token('$username', new TokenType(TokenType::VARIABLE_TYPE));
        $expected = new VariableValue('username');

        $parser = new VariableTokenParser();
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);
    }
}
