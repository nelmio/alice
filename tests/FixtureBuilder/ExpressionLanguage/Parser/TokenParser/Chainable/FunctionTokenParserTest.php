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

use Nelmio\Alice\Definition\MethodCall\IdentityFactory;
use Nelmio\Alice\Definition\Value\EvaluatedValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\ValueForCurrentValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FunctionTokenParser
 */
class FunctionTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(FunctionTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new FunctionTokenParser();
    }

    public function testCanParseFunctionsTokens()
    {
        $token = new Token('', new TokenType(TokenType::FUNCTION_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new FunctionTokenParser();

        $this->assertTrue($parser->canParse($token));
        $this->assertFalse($parser->canParse($anotherToken));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParserNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.
     */
    public function testThrowsAnExceptionIfNoDecoratedParserIsFound()
    {
        $token = new Token('', new TokenType(TokenType::FUNCTION_TYPE));
        $parser = new FunctionTokenParser();

        $parser->parse($token);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException
     * @expectedExceptionMessage Could not parse the token "" (type: FUNCTION_TYPE).
     */
    public function testThrowsAnExceptionIfCouldNotParseToken()
    {
        $token = new Token('', new TokenType(TokenType::FUNCTION_TYPE));
        $parser = new FunctionTokenParser(new FakeParser());

        $parser->parse($token);
    }

    public function testReturnsFunctionValue()
    {
        $token = new Token('<foo()>', new TokenType(TokenType::FUNCTION_TYPE));
        $expected = new FunctionCallValue('foo');

        $parser = new FunctionTokenParser(new FakeParser());
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);
    }

    public function testIfFunctionHasArgumentsThenEachArgumentWillBeParsed()
    {
        $token = new Token('<foo(arg1, arg2)>', new TokenType(TokenType::FUNCTION_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('arg1')->willReturn('parsed_arg1');
        $decoratedParserProphecy->parse('arg2')->willReturn('parsed_arg2');
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new FunctionCallValue('foo', ['parsed_arg1', 'parsed_arg2']);

        $parser = new FunctionTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function testEachArgumentIsTrimedToNotFalsifyTheParsing()
    {
        $token = new Token('<foo( arg1 , arg2 )>', new TokenType(TokenType::FUNCTION_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('arg1')->willReturn('parsed_arg1');
        $decoratedParserProphecy->parse('arg2')->willReturn('parsed_arg2');
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new FunctionCallValue('foo', ['parsed_arg1', 'parsed_arg2']);

        $parser = new FunctionTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function testArgumentsQuotesAreRemoved()
    {
        $token = new Token('<foo( "arg1" , \'arg2\' )>', new TokenType(TokenType::FUNCTION_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('arg1')->willReturn('parsed_arg1');
        $decoratedParserProphecy->parse('arg2')->willReturn('parsed_arg2');
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new FunctionCallValue('foo', ['parsed_arg1', 'parsed_arg2']);

        $parser = new FunctionTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function testCanHandleASingleArgument()
    {
        $token = new Token('<foo(arg)>', new TokenType(TokenType::FUNCTION_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('arg')->willReturn('parsed_arg');
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new FunctionCallValue('foo', ['parsed_arg']);

        $parser = new FunctionTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testCanHandleNoArguments()
    {
        $token = new Token('<foo()>', new TokenType(TokenType::FUNCTION_TYPE));
        $anotherToken = new Token('<foo(  )>', new TokenType(TokenType::FUNCTION_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new FunctionCallValue('foo');

        $parser = new FunctionTokenParser($decoratedParser);
        $actual0 = $parser->parse($token);
        $actual1 = $parser->parse($anotherToken);

        $this->assertEquals($expected, $actual0);
        $this->assertEquals($expected, $actual1);
    }

    public function testDoesNotParseArgumentsIfFunctionIsIdentity()
    {
        $token = new Token('<identity( arg0 , arg1 )>', new TokenType(TokenType::FUNCTION_TYPE));

        $expected = IdentityFactory::create(' arg0 , arg1 ');

        $parser = new FunctionTokenParser(new FakeParser());
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf(EvaluatedValue::class, $actual->getArguments()[0]);
    }

    public function testCanParseArgumentsForCurrentValue()
    {
        // Arguments should be discarded
        $token = new Token('<current( arg0 , arg1 )>', new TokenType(TokenType::FUNCTION_TYPE));

        $expected = new FunctionCallValue('current', [new ValueForCurrentValue()]);

        $parser = new FunctionTokenParser(new FakeParser());
        $actual = $parser->parse($token);

        $this->assertEquals($expected, $actual);
        $this->assertInstanceOf(ValueForCurrentValue::class, $actual->getArguments()[0]);
    }
}
