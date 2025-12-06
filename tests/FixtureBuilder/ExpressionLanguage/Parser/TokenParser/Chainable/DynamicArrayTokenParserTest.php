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

use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\Value\FakeValue;
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
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(DynamicArrayTokenParser::class)]
final class DynamicArrayTokenParserTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableTokenParser(): void
    {
        self::assertTrue(is_a(DynamicArrayTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(DynamicArrayTokenParser::class))->isCloneable());
    }

    public function testCanParseDynamicArrayTokens(): void
    {
        $token = new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new DynamicArrayTokenParser();

        self::assertTrue($parser->canParse($token));
        self::assertFalse($parser->canParse($anotherToken));
    }

    public function testThrowsAnExceptionIfNoDecoratedParserIsFound(): void
    {
        $token = new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $parser = new DynamicArrayTokenParser();

        $this->expectException(ParserNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.');

        $parser->parse($token);
    }

    public function testThrowsAnExceptionIfCouldNotParseToken(): void
    {
        $token = new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $parser = new DynamicArrayTokenParser(new FakeParser());

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "" (type: DYNAMIC_ARRAY_TYPE).');

        $parser->parse($token);
    }

    public function testReturnsADynamicArrayIfCanParseToken(): void
    {
        $token = new Token('10x @user', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('10')->willReturn('0');
        $decoratedParserProphecy->parse('@user')->willReturn('parsed_element');
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new DynamicArrayValue(0, 'parsed_element');

        $parser = new DynamicArrayTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function testParsedDynamicArrayQuantifierCanBeAValue(): void
    {
        $token = new Token('10x @user', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('10')->willReturn(new FakeValue());
        $decoratedParserProphecy->parse('@user')->willReturn('parsed_element');
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new DynamicArrayValue(new FakeValue(), 'parsed_element');

        $parser = new DynamicArrayTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }
}
