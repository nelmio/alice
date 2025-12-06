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

use Nelmio\Alice\Definition\Value\ArrayValue;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParserNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @internal
 */
#[CoversClass(StringArrayTokenParser::class)]
final class StringArrayTokenParserTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableTokenParser(): void
    {
        self::assertTrue(is_a(StringArrayTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(StringArrayTokenParser::class))->isCloneable());
    }

    public function testCanParseDynamicArrayTokens(): void
    {
        $token = new Token('', new TokenType(TokenType::STRING_ARRAY_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new StringArrayTokenParser();

        self::assertTrue($parser->canParse($token));
        self::assertFalse($parser->canParse($anotherToken));
    }

    public function testThrowsAnExceptionIfNoDecoratedParserIsFound(): void
    {
        $token = new Token('', new TokenType(TokenType::STRING_ARRAY_TYPE));
        $parser = new StringArrayTokenParser();

        $this->expectException(ParserNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.');

        $parser->parse($token);
    }

    public function testThrowsAnErrorIfCouldNotParseToken(): void
    {
        try {
            $token = new Token('', new TokenType(TokenType::STRING_ARRAY_TYPE));
            $parser = new StringArrayTokenParser(new FakeParser());

            $parser->parse($token);
        } catch (ParseException $exception) {
            self::assertEquals(
                'Could not parse the token "" (type: STRING_ARRAY_TYPE).',
                $exception->getMessage(),
            );
            self::assertEquals(0, $exception->getCode());
            self::assertNotNull($exception->getPrevious());
        }
    }

    public function testParsesEachArrayElementAndReturnsTheConstructedArray(): void
    {
        $token = new Token('[val1, val2]', new TokenType(TokenType::STRING_ARRAY_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('val1')->willReturn('parsed_val1');
        $decoratedParserProphecy->parse('val2')->willReturn('parsed_val2');
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new ArrayValue(['parsed_val1', 'parsed_val2']);

        $parser = new StringArrayTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function testIsAbleToParseEmptyArrays(): void
    {
        $token = new Token('[]', new TokenType(TokenType::STRING_ARRAY_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new ArrayValue([]);

        $parser = new StringArrayTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);
    }

    public function testTrimsEachArgumentValueBeforePassingThemToTheDecoratedParser(): void
    {
        $token = new Token('[ val1 , val2 ]', new TokenType(TokenType::STRING_ARRAY_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('val1')->willReturn('parsed_val1');
        $decoratedParserProphecy->parse('val2')->willReturn('parsed_val2');
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new ArrayValue(['parsed_val1', 'parsed_val2']);

        $parser = new StringArrayTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);
    }
}
