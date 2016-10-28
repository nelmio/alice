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

use Nelmio\Alice\Definition\Value\ChoiceListValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureListReferenceTokenParser
 */
class FixtureListReferenceTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(FixtureListReferenceTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new FixtureListReferenceTokenParser();
    }

    /**
     * @dataProvider provideTokens
     */
    public function testCanParseListReferenceTokens(Token $token, bool $expected)
    {
        $parser = new FixtureListReferenceTokenParser();
        $actual = $parser->canParse($token);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException
     * @expectedExceptionMessage Could not parse the token "" (type: LIST_REFERENCE_TYPE).
     */
    public function testThrowsAnExceptionIfInvalidTokenIsGiven()
    {
        $token = new Token('', new TokenType(TokenType::LIST_REFERENCE_TYPE));

        $parser = new FixtureListReferenceTokenParser();
        $parser->parse($token);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException
     * @expectedExceptionMessage Could not parse the token "" (type: LIST_REFERENCE_TYPE).
     */
    public function testThrowsAnExceptionIfAMalformedTokenIsGiven()
    {
        $token = new Token('', new TokenType(TokenType::LIST_REFERENCE_TYPE));

        $parser = new FixtureListReferenceTokenParser();
        $parser->parse($token);
    }

    public function testReturnsListOfPossibleValues()
    {
        $token = new Token('@user_{alice, bob}', new TokenType(TokenType::LIST_REFERENCE_TYPE));
        $expected = new ChoiceListValue([
            new FixtureReferenceValue('user_alice'),
            new FixtureReferenceValue('user_bob'),
        ]);

        $parser = new FixtureListReferenceTokenParser();
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
                new Token('', new TokenType(TokenType::LIST_REFERENCE_TYPE)),
                true,
            ],
        ];
    }
}
