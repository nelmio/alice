<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;

/**
 * @covers Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\EscapedTokenParser
 */
class EscapedTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(EscapedTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new EscapedTokenParser();
    }

    /**
     * @dataProvider provideTokens
     */
    public function testCanParseEscapedTokens(Token $token, bool $expected)
    {
        $parser = new EscapedTokenParser();
        $actual = $parser->canParse($token);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException
     * @expectedExceptionMessage Could not parse the token "" (type: ESCAPED_ARROW_TYPE).
     */
    public function testThrowsAnExceptionIfAMalformedTokenIsGiven()
    {
        $token = new Token('', new TokenType(TokenType::ESCAPED_ARROW_TYPE));

        $parser = new EscapedTokenParser();
        $parser->parse($token);
    }

    public function testReturnsEscapedValue()
    {
        $token = new Token('<<', new TokenType(TokenType::ESCAPED_ARROW_TYPE));
        $expected = '<';

        $parser = new EscapedTokenParser();
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);
    }

    public function provideTokens()
    {
        return [
            [
                new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE)),
                false,
            ],
            [
                new Token('', new TokenType(TokenType::ESCAPED_ARROW_TYPE)),
                true,
            ],
            [
                new Token('', new TokenType(TokenType::ESCAPED_REFERENCE_TYPE)),
                true,
            ],
            [
                new Token('', new TokenType(TokenType::ESCAPED_VARIABLE_TYPE)),
                true,
            ],
        ];
    }
}
