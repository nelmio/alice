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
 * @covers Nelmio\Alice\ExpressionLanguage\Parser\TokenParser\Chainable\IdentityTokenParser
 */
class IdentityTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(IdentityTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new IdentityTokenParser();
    }

    public function testCanParseIdentityTokens()
    {
        $token = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::ESCAPED_ARROW_TYPE));
        $parser = new IdentityTokenParser();

        $this->assertTrue($parser->canParse($token));
        $this->assertFalse($parser->canParse($anotherToken));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\ExpressionLanguage\ParserNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.
     */
    public function testThrowsAnExceptionIfNoDecoratedParserIsFound()
    {
        $token = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new IdentityTokenParser();

        $parser->parse($token);
    }

    public function testUsesDecoratedParserToParseTokenReturnsADynamicArrayIfCanParseToken()
    {
        $token = new Token('<( foo )>', new TokenType(TokenType::IDENTITY_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('<identity( foo )>')->willReturn('parsed_function');
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = 'parsed_function';

        $parser = new IdentityTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
