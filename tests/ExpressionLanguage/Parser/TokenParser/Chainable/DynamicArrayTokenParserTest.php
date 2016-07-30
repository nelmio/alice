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
 * @covers Nelmio\Alice\ExpressionLanguage\Parser\TokenParser\Chainable\DynamicArrayTokenParser
 */
class DynamicArrayTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(DynamicArrayTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new DynamicArrayTokenParser();
    }

    public function testCanParseDynamicArrayTokens()
    {
        $token = new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $parser = new DynamicArrayTokenParser();

        $this->assertTrue($parser->canParse($token));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\ExpressionLanguage\ParserNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.
     */
    public function testThrowsAnExceptionIfNoDecoratedParserIsFound()
    {
        $token = new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $parser = new DynamicArrayTokenParser();

        $parser->parse($token);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\ExpressionLanguage\ParseException
     * @expectedExceptionMessage Could not parse the dynamic array "".
     */
    public function testThrowsAnExceptionIfCouldNotParseToken()
    {
        $token = new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $parser = new DynamicArrayTokenParser(new FakeParser());

        $parser->parse($token);
    }

    public function testReturnsADynamicArrayIfCanParseToken()
    {
        $token = new Token('10x @user', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('10')->willReturn('parsed_quantifier');
        $decoratedParserProphecy->parse('@user')->willReturn('parsed_element');
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new DynamicArrayValue('parsed_quantifier', 'parsed_element');

        $parser = new DynamicArrayTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);
    }
}
