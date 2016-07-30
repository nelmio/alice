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
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\ExpressionLanguage\Parser\TokenParser\Chainable\MethodReferenceTokenParser
 */
class MethodReferenceTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(MethodReferenceTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new MethodReferenceTokenParser();
    }

    public function testCanParseMethodTokens()
    {
        $token = new Token('', new TokenType(TokenType::METHOD_REFERENCE_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new MethodReferenceTokenParser();

        $this->assertTrue($parser->canParse($token));
        $this->assertFalse($parser->canParse($anotherToken));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\ExpressionLanguage\ParserNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.
     */
    public function testThrowsAnExceptionIfNoDecoratedParserIsFound()
    {
        $token = new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $parser = new MethodReferenceTokenParser();

        $parser->parse($token);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\ExpressionLanguage\ParseException
     * @expectedExceptionMessage Could not parse the dynamic array "".
     */
    public function testThrowsAnExceptionIfCouldNotParseToken()
    {
        $token = new Token('', new TokenType(TokenType::METHOD_REFERENCE_TYPE));
        $parser = new MethodReferenceTokenParser(new FakeParser());

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

        $parser = new MethodReferenceTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }
}
