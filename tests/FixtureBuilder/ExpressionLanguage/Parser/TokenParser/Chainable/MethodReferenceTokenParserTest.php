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

use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParserNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\MethodReferenceTokenParser
 */
class MethodReferenceTokenParserTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableTokenParser(): void
    {
        static::assertTrue(is_a(MethodReferenceTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(MethodReferenceTokenParser::class))->isCloneable());
    }

    public function testCanParseMethodTokens(): void
    {
        $token = new Token('', new TokenType(TokenType::METHOD_REFERENCE_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new MethodReferenceTokenParser();

        static::assertTrue($parser->canParse($token));
        static::assertFalse($parser->canParse($anotherToken));
    }

    public function testThrowsAnExceptionIfNoDecoratedParserIsFound(): void
    {
        $token = new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $parser = new MethodReferenceTokenParser();

        $this->expectException(ParserNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.');

        $parser->parse($token);
    }

    public function testThrowsAnExceptionIfCouldNotParseToken(): void
    {
        $token = new Token('', new TokenType(TokenType::METHOD_REFERENCE_TYPE));
        $parser = new MethodReferenceTokenParser(new FakeParser());

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "" (type: METHOD_REFERENCE_TYPE).');

        $parser->parse($token);
    }

    public function testThrowsAnExceptionIfParsingReferenceGivesAnUnexpectedResult(): void
    {
        $token = new Token('@@malformed_user->getUserName(arg1, arg2)', new TokenType(TokenType::METHOD_REFERENCE_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('@@malformed_user')->willReturn('string value')
        ;
        $decoratedParserProphecy
            ->parse('<getUserName(arg1, arg2)>')
            ->willReturn($call = new FunctionCallValue('getUserName', ['parsed_arg1', 'parsed_arg2']))
        ;
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new MethodReferenceTokenParser($decoratedParser);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "@@malformed_user->getUserName(arg1, arg2)" (type: METHOD_REFERENCE_TYPE).');

        $parser->parse($token);
    }

    public function testThrowsAnExceptionIfParsingFunctionCallGivesAnUnexpectedResult(): void
    {
        $token = new Token('@user->getUserName((arg1, arg2)', new TokenType(TokenType::METHOD_REFERENCE_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy
            ->parse('@user')
            ->willReturn($reference = new FixtureReferenceValue('user'))
        ;
        $decoratedParserProphecy
            ->parse('<getUserName((arg1, arg2)>')
            ->willReturn('string value')
        ;
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new MethodReferenceTokenParser($decoratedParser);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "@user->getUserName((arg1, arg2)" (type: METHOD_REFERENCE_TYPE).');

        $parser->parse($token);
    }

    public function testReturnsAFixtureMethodCallValueIfCanParseToken(): void
    {
        $token = new Token('@user->getUserName(arg1, arg2)', new TokenType(TokenType::METHOD_REFERENCE_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy
            ->parse('@user')
            ->willReturn($reference = new FixtureReferenceValue('user'))
        ;
        $decoratedParserProphecy
            ->parse('<getUserName(arg1, arg2)>')
            ->willReturn($call = new FunctionCallValue('getUserName', ['parsed_arg1', 'parsed_arg2']))
        ;
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new FixtureMethodCallValue($reference, $call);

        $parser = new MethodReferenceTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        static::assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }
}
