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
 * @covers Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\EscapedArrayTokenParser
 */
class EscapedArrayTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(EscapedArrayTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new EscapedArrayTokenParser();
    }

    public function testCanParseEscapedArrayTokens()
    {
        $token = new Token('', new TokenType(TokenType::ESCAPED_ARRAY_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new EscapedArrayTokenParser();

        $this->assertTrue($parser->canParse($token));
        $this->assertFalse($parser->canParse($anotherToken));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException
     * @expectedExceptionMessage Could not parse the token "" (type: ESCAPED_ARRAY_TYPE).
     */
    public function testThrowsAnErrorIfAMalformedTokenIsGiven()
    {
        $token = new Token('', new TokenType(TokenType::ESCAPED_ARRAY_TYPE));

        $parser = new EscapedArrayTokenParser();
        $parser->parse($token);
    }

    public function testReturnsEscapedValue()
    {
        $token = new Token('[[ X ]]', new TokenType(TokenType::ESCAPED_ARRAY_TYPE));
        $expected = '[ X ]';

        $parser = new EscapedArrayTokenParser();
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);
    }
}
