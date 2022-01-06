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

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\IdentityTokenParser
 */
class IdentityTokenParserTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableTokenParser(): void
    {
        static::assertTrue(is_a(IdentityTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(IdentityTokenParser::class))->isCloneable());
    }

    public function testCanParseIdentityTokens(): void
    {
        $token = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::ESCAPED_VALUE_TYPE));
        $parser = new IdentityTokenParser(new FakeChainableTokenParser());

        static::assertTrue($parser->canParse($token));
        static::assertFalse($parser->canParse($anotherToken));
    }

    public function testReplaceIdentityIntoAFunctionCallBeforeHandingItOverToItsDecorated(): void
    {
        $token = new Token('<(echo "hello world!")>', new TokenType(TokenType::IDENTITY_TYPE));

        $decoratedParserProphecy = $this->prophesize(ChainableTokenParserInterface::class);
        $decoratedParserProphecy
            ->parse(
                new Token('<identity(echo "hello world!")>', new TokenType(TokenType::FUNCTION_TYPE))
            )
            ->willReturn($expected = 'foo')
        ;
        /** @var ChainableTokenParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new IdentityTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        static::assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testSupportNewlines(): void
    {
        $token = new Token("<(new DateTime(\n    '2021-12-29',\n))>", new TokenType(TokenType::IDENTITY_TYPE));

        $decoratedParserProphecy = $this->prophesize(ChainableTokenParserInterface::class);
        $decoratedParserProphecy
            ->parse(
                new Token("<identity(new DateTime(\n    '2021-12-29',\n))>", new TokenType(TokenType::FUNCTION_TYPE))
            )
            ->willReturn($expected = 'foo')
        ;
        /** @var ChainableTokenParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $parser = new IdentityTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        static::assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
