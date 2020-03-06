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

use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParserNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\PropertyReferenceTokenParser
 */
class PropertyReferenceTokenParserTest extends TestCase
{
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(PropertyReferenceTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(PropertyReferenceTokenParser::class))->isCloneable());
    }

    public function testCanParseDynamicArrayTokens()
    {
        $token = new Token('', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new PropertyReferenceTokenParser();

        $this->assertTrue($parser->canParse($token));
        $this->assertFalse($parser->canParse($anotherToken));
    }

    public function testThrowsAnExceptionIfNoDecoratedParserIsFound()
    {
        $token = new Token('', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE));
        $parser = new PropertyReferenceTokenParser();

        $this->expectException(ParserNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.');

        $parser->parse($token);
    }

    public function testThrowsAnExceptionIfCouldNotParseToken()
    {
        $token = new Token('', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE));
        $parser = new PropertyReferenceTokenParser(new FakeParser());

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "" (type: PROPERTY_REFERENCE_TYPE).');

        $parser->parse($token);
    }

    public function testThrowsAnExceptionIfParsingReferenceReturnsUnexpectedResult()
    {
        $token = new Token('@@malformed_user->username', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('@@malformed_user')->willReturn('string value');
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new PropertyReferenceTokenParser($decoratedParser);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "@@malformed_user->username" (type: PROPERTY_REFERENCE_TYPE).');

        $parser->parse($token);
    }

    public function testReturnsAPropertyReferenceIfCanParseToken()
    {
        $token = new Token('@user->username', new TokenType(TokenType::PROPERTY_REFERENCE_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('@user')->willReturn($reference = new FixtureReferenceValue('user'));
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new FixturePropertyValue($reference, 'username');

        $parser = new PropertyReferenceTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
