<?php

/**
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable;

use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureMethodReferenceTokenParser
 */
class FixtureMethodReferenceTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(FixtureMethodReferenceTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new FixtureMethodReferenceTokenParser();
    }

    public function testCanParseMethodReferenceTokens()
    {
        $token = new Token('', new TokenType(TokenType::METHOD_REFERENCE_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new FixtureMethodReferenceTokenParser();

        $this->assertTrue($parser->canParse($token));
        $this->assertFalse($parser->canParse($anotherToken));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParserNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.
     */
    public function testThrowsAnExceptionIfNoDecoratedParserIsFound()
    {
        $token = new Token('', new TokenType(TokenType::METHOD_REFERENCE_TYPE));
        $parser = new FixtureMethodReferenceTokenParser();

        $parser->parse($token);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException
     * @expectedExceptionMessage Could not parse the token "" (type: METHOD_REFERENCE_TYPE).
     */
    public function testThrowsAnExceptionIfCouldNotParseToken()
    {
        $token = new Token('', new TokenType(TokenType::METHOD_REFERENCE_TYPE));
        $parser = new FixtureMethodReferenceTokenParser(new FakeParser());

        $parser->parse($token);
    }

    public function testReturnsFunctionValue()
    {
        $token = new Token('@user->getName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('@user')->willReturn($reference = new FixtureReferenceValue('user'));
        $decoratedParserProphecy->parse('<getName()>')->willReturn($call = new FunctionCallValue('getName'));
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new FixtureMethodCallValue($reference, $call);

        $parser = new FixtureMethodReferenceTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException
     * @expectedExceptionMessage Could not parse the token "@user->getName()->anotherName()" (type: METHOD_REFERENCE_TYPE).
     */
    public function testThrowsAnExceptionIfMethodReferenceIsMalformed()
    {
        $token = new Token('@user->getName()->anotherName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE));

        $parser = new FixtureMethodReferenceTokenParser(new FakeParser());
        $parser->parse($token);
    }

    /**
     * @dataProvider             provideParser
     *
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException
     * @expectedExceptionMessage Could not parse the token "@user->getName()" (type: METHOD_REFERENCE_TYPE).
     */
    public function testThrowsAnExceptionIfParsingReturnsAnUnexpectedResult(ParserInterface $decoratedParser)
    {
        $token = new Token('@user->getName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE));

        $parser = new FixtureMethodReferenceTokenParser($decoratedParser);
        $parser->parse($token);
    }

    public function provideParser()
    {
        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('@user')->willReturn('foo');
        $decoratedParserProphecy->parse('<getName()>')->willReturn(new FunctionCallValue('getName'));

        yield 'unexpected reference' => [$decoratedParserProphecy->reveal()];

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('@user')->willReturn(new FixtureReferenceValue('user'));
        $decoratedParserProphecy->parse('<getName()>')->willReturn('foo');

        yield 'unexpected fixture call' => [$decoratedParserProphecy->reveal()];

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('@user')->willReturn('foo');
        $decoratedParserProphecy->parse('<getName()>')->willReturn('bar');

        yield 'unexpected reference and fixture call' => [$decoratedParserProphecy->reveal()];
    }
}
