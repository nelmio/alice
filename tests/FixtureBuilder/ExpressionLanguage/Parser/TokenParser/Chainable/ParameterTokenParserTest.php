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
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(ParameterTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ParameterTokenParser::class))->isCloneable());
    }

    public function testCanParseMethodTokens()
    {
        $token = new Token('', new TokenType(TokenType::PARAMETER_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new ParameterTokenParser();

        $this->assertTrue($parser->canParse($token));
        $this->assertFalse($parser->canParse($anotherToken));
    }

    public function testThrowsAnErrorIfPassedParameterIsMalformed()
    {
        try {
            $token = new Token('', new TokenType(TokenType::PARAMETER_TYPE));
            $parser = new ParameterTokenParser();

            $parser->parse($token);
            $this->fail('Expected exception to be thrown.');
        } catch (ParseException $exception) {
            $this->assertEquals(
                'Could not parse the token "" (type: PARAMETER_TYPE).',
                $exception->getMessage()
            );
            $this->assertEquals(0, $exception->getCode());
            $this->assertNotNull($exception->getPrevious());
        }
    }

    public function testReturnsAParameterValueIfCanParseToken()
    {
        $token = new Token('<{param}>', new TokenType(TokenType::PARAMETER_TYPE));
        $expected = new ParameterValue('param');

        $parser = new ParameterTokenParser();
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);
    }
}
