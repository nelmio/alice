<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\Definition\Value\VariableValue;
use Nelmio\Alice\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

/**
 * @covers Nelmio\Alice\ExpressionLanguage\Parser\TokenParser\Chainable\VariableTokenParser
 */
class VariableTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(VariableTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new VariableTokenParser();
    }

    public function testCanParseDynamicArrayTokens()
    {
        $token = new Token('', new TokenType(TokenType::VARIABLE_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new VariableTokenParser();

        $this->assertTrue($parser->canParse($token));
        $this->assertFalse($parser->canParse($anotherToken));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\ExpressionLanguage\ParseException
     * @expectedExceptionMessage Could not parse the token "" (type: VARIABLE_TYPE).
     */
    public function testThrowsAnExceptionIfCouldNotParseToken()
    {
        $token = new Token('', new TokenType(TokenType::VARIABLE_TYPE));
        $parser = new VariableTokenParser(new FakeParser());

        $parser->parse($token);
    }

    public function testReturnsADynamicArrayIfCanParseToken()
    {
        $token = new Token('$username', new TokenType(TokenType::VARIABLE_TYPE));
        $expected = new VariableValue('username');

        $parser = new VariableTokenParser();
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);
    }
}
