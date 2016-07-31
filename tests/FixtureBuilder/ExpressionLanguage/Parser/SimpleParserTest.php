<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser;

use Nelmio\Alice\Definition\Value\ChoiceListValue;
use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\OptionalValue;
use Nelmio\Alice\Definition\Value\ParameterValue;
use Nelmio\Alice\Definition\Value\ListValue;
use Nelmio\Alice\Definition\Value\VariableValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FakeLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\DummyChainableTokenParserAware;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\FakeTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\Throwable\ParseThrowable;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\SimpleParser
 */
class SimpleParserTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAParser()
    {
        $this->assertTrue(is_a(SimpleParser::class, ParserInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new SimpleParser(new FakeLexer(), new FakeTokenParser());
    }

    public function testCanBeInstantiatedWithALexerAndAParser()
    {
        new SimpleParser(new FakeLexer(), new FakeTokenParser());
    }

    public function testIfParserIsParserAwareThenItInjectsItselfToIt()
    {
        $decoratedParser = new DummyChainableTokenParserAware();
        $parser = new SimpleParser(new FakeLexer(), $decoratedParser);

        $this->assertSame($parser, $decoratedParser->parser);
    }

    public function testLexValueAndParsesEachTokenToReturnAValue()
    {
        $value = 'foo';

        $lexerProphecy = $this->prophesize(LexerInterface::class);
        $lexerProphecy->lex($value)->willReturn([
            $token1 = new Token('foo', new TokenType(TokenType::STRING_TYPE)),
            $token2 = new Token('bar', new TokenType(TokenType::VARIABLE_TYPE)),
        ]);
        /** @var LexerInterface $lexer */
        $lexer = $lexerProphecy->reveal();

        $tokenParserProphecy = $this->prophesize(TokenParserInterface::class);
        $tokenParserProphecy->parse($token1)->willReturn('parsed_foo');
        $tokenParserProphecy->parse($token2)->willReturn('parsed_bar');
        /** @var TokenParserInterface $tokenParser */
        $tokenParser = $tokenParserProphecy->reveal();

        $parser = new SimpleParser($lexer, $tokenParser);
        $parser->parse($value);

        $lexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
        $tokenParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function testIfTheLexProcessReturnsMultipleTokensThenTheValueReturnedWillBeAListValue()
    {
        $value = 'foo';

        $lexerProphecy = $this->prophesize(LexerInterface::class);
        $lexerProphecy->lex($value)->willReturn([
            $token1 = new Token('foo', new TokenType(TokenType::STRING_TYPE)),
            $token2 = new Token('bar', new TokenType(TokenType::VARIABLE_TYPE)),
        ]);
        /** @var LexerInterface $lexer */
        $lexer = $lexerProphecy->reveal();

        $tokenParserProphecy = $this->prophesize(TokenParserInterface::class);
        $tokenParserProphecy->parse($token1)->willReturn(new ParameterValue('parsed_foo'));
        $tokenParserProphecy->parse($token2)->willReturn('parsed_bar');
        /** @var TokenParserInterface $tokenParser */
        $tokenParser = $tokenParserProphecy->reveal();

        $parser = new SimpleParser($lexer, $tokenParser);
        $parsedValue = $parser->parse($value);

        $this->assertEquals(
            new ListValue([
                new ParameterValue('parsed_foo'),
                'parsed_bar',
            ]),
            $parsedValue
        );
    }

    public function testIfOnlyOneTokensFoundThenReturnsASimpleValue()
    {
        $value = 'foo';

        $lexerProphecy = $this->prophesize(LexerInterface::class);
        $lexerProphecy->lex($value)->willReturn([
            $token1 = new Token('foo', new TokenType(TokenType::STRING_TYPE)),
        ]);
        /** @var LexerInterface $lexer */
        $lexer = $lexerProphecy->reveal();

        $tokenParserProphecy = $this->prophesize(TokenParserInterface::class);
        $tokenParserProphecy->parse($token1)->willReturn('parsed_foo');
        /** @var TokenParserInterface $tokenParser */
        $tokenParser = $tokenParserProphecy->reveal();

        $parser = new SimpleParser($lexer, $tokenParser);
        $parsedValue = $parser->parse($value);

        $this->assertEquals(
            'parsed_foo',
            $parsedValue
        );
    }
}
