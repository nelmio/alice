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

use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;

/**
 * @covers Nelmio\Alice\ExpressionLanguage\Parser\TokenParser\Chainable\EscapedTokenParser
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
