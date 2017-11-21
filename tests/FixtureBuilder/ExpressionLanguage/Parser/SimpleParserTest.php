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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser;

use Nelmio\Alice\Definition\Value\ListValue;
use Nelmio\Alice\Definition\Value\NestedValue;
use Nelmio\Alice\Definition\Value\ParameterValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\FakeLexer;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\LexerInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\DummyChainableTokenParserAware;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\FakeTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\SimpleParser
 */
class SimpleParserTest extends TestCase
{
    public function testIsAParser()
    {
        $this->assertTrue(is_a(SimpleParser::class, ParserInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(SimpleParser::class))->isCloneable());
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

    public function testIfATokenIsParsedIntoANestedValueThenItsValuesAreMerged()
    {
        $value = 'foo';

        $lexerProphecy = $this->prophesize(LexerInterface::class);
        $lexerProphecy->lex($value)->willReturn([
            $token1 = new Token('foo', new TokenType(TokenType::STRING_TYPE)),
            $token2 = new Token('bar', new TokenType(TokenType::VARIABLE_TYPE)),
            $token3 = new Token('baz', new TokenType(TokenType::FUNCTION_TYPE)),
        ]);
        /** @var LexerInterface $lexer */
        $lexer = $lexerProphecy->reveal();

        $tokenParserProphecy = $this->prophesize(TokenParserInterface::class);
        $tokenParserProphecy->parse($token1)->willReturn('parsed_foo');
        $tokenParserProphecy
            ->parse($token2)
            ->willReturn(
                new NestedValue([
                    'first',
                    'second',
                ])
            )
        ;
        $tokenParserProphecy->parse($token3)->willReturn('parsed_baz');
        /** @var TokenParserInterface $tokenParser */
        $tokenParser = $tokenParserProphecy->reveal();

        $expected = new ListValue([
            'parsed_foo',
            'first',
            'second',
            'parsed_baz',
        ]);

        $parser = new SimpleParser($lexer, $tokenParser);
        $actual = $parser->parse($value);

        $this->assertEquals($expected, $actual);

        $lexerProphecy->lex(Argument::any())->shouldHaveBeenCalledTimes(1);
        $tokenParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(3);
    }
}
