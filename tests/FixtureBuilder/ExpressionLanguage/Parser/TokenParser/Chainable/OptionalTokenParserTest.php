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

use Nelmio\Alice\Definition\Value\OptionalValue;
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
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\OptionalTokenParser
 * @internal
 */
class OptionalTokenParserTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableTokenParser(): void
    {
        self::assertTrue(is_a(OptionalTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(OptionalTokenParser::class))->isCloneable());
    }

    public function testCanParseMethodTokens(): void
    {
        $token = new Token('', new TokenType(TokenType::OPTIONAL_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new OptionalTokenParser();

        self::assertTrue($parser->canParse($token));
        self::assertFalse($parser->canParse($anotherToken));
    }

    public function testThrowsAnExceptionIfNoDecoratedParserIsFound(): void
    {
        $token = new Token('', new TokenType(TokenType::OPTIONAL_TYPE));
        $parser = new OptionalTokenParser();

        $this->expectException(ParserNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.');

        $parser->parse($token);
    }

    public function testThrowsAnExceptionIfCouldNotParseToken(): void
    {
        $token = new Token('', new TokenType(TokenType::OPTIONAL_TYPE));
        $parser = new OptionalTokenParser(new FakeParser());

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "" (type: OPTIONAL_TYPE).');

        $parser->parse($token);
    }

    public function testReturnsAnOptionalValueIfCanParseToken(): void
    {
        $token = new Token('60%? foo: bar', new TokenType(TokenType::OPTIONAL_TYPE));
        $anotherToken = new Token('80%? baz', new TokenType(TokenType::OPTIONAL_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('60')->willReturn('parsed_quantifier');
        $decoratedParserProphecy->parse('foo')->willReturn('parsed_first_member');
        $decoratedParserProphecy->parse('bar')->willReturn('parsed_second_member');
        $decoratedParserProphecy->parse('80')->willReturn('parsed_80');
        $decoratedParserProphecy->parse('baz')->willReturn('parsed_baz');
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected0 = new OptionalValue('parsed_quantifier', 'parsed_first_member', 'parsed_second_member');
        $expected1 = new OptionalValue('parsed_80', 'parsed_baz');

        $parser = new OptionalTokenParser($decoratedParser);
        $actual0 = $parser->parse($token);
        $actual1 = $parser->parse($anotherToken);

        self::assertEquals($expected0, $actual0);
        self::assertEquals($expected1, $actual1);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(5);
    }
}
