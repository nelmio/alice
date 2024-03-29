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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ChainableTokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FakeChainableTokenParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\ProphecyChainableTokenParserAware;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParserInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParserNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use ReflectionProperty;
use stdClass;
use TypeError;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\TokenParserRegistry
 * @internal
 */
class TokenParserRegistryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ReflectionProperty
     */
    private $parsersRefl;

    protected function setUp(): void
    {
        $this->parsersRefl = (new ReflectionClass(TokenParserRegistry::class))->getProperty('parsers');
        $this->parsersRefl->setAccessible(true);
    }

    public function testIsATokenParser(): void
    {
        self::assertTrue(is_a(TokenParserRegistry::class, TokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(TokenParserRegistry::class))->isCloneable());
    }

    public function testAcceptsOnlyChainableParsers(): void
    {
        new TokenParserRegistry([new FakeChainableTokenParser()]);

        $this->expectException(TypeError::class);

        new TokenParserRegistry([new stdClass()]);
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $parser = new FakeParser();

        $parserAwareProphecy = $this->prophesize(ParserAwareInterface::class);
        $parserAwareProphecy->withParser($parser)->willReturn($returnedParser = new FakeChainableTokenParser());
        /** @var ParserAwareInterface $parserAware */
        $parserAware = $parserAwareProphecy->reveal();

        $tokenParser = new TokenParserRegistry([
            $parser1 = new FakeChainableTokenParser(),
            $parser2 = new ProphecyChainableTokenParserAware(new FakeChainableTokenParser(), $parserAware),
        ]);

        $newTokenParser = $tokenParser->withParser($parser);

        self::assertInstanceOf(TokenParserRegistry::class, $newTokenParser);

        self::assertSame(
            [
                $parser1,
                $parser2,
            ],
            $this->parsersRefl->getValue($tokenParser),
        );

        $newTokenParserParsers = $this->parsersRefl->getValue($newTokenParser);
        self::assertCount(2, $newTokenParserParsers);
        self::assertEquals(
            [
                $parser1,
                $returnedParser,
            ],
            $newTokenParserParsers,
        );
    }

    public function testPicksTheFirstSuitableParserToParseTheToken(): void
    {
        $token = new Token('foo', new TokenType(TokenType::STRING_TYPE));
        $expected = 'foo';

        $parser1Prophecy = $this->prophesize(ChainableTokenParserInterface::class);
        $parser1Prophecy->canParse($token)->willReturn(false);
        /** @var ChainableTokenParserInterface $parser1 */
        $parser1 = $parser1Prophecy->reveal();

        $parser2Prophecy = $this->prophesize(ChainableTokenParserInterface::class);
        $parser2Prophecy->canParse($token)->willReturn(true);
        $parser2Prophecy->parse($token)->willReturn($expected);
        /** @var ChainableTokenParserInterface $parser2 */
        $parser2 = $parser2Prophecy->reveal();

        $parser3Prophecy = $this->prophesize(ChainableTokenParserInterface::class);
        $parser3Prophecy->canParse(Argument::any())->shouldNotBeCalled();
        /** @var ChainableTokenParserInterface $parser3 */
        $parser3 = $parser3Prophecy->reveal();

        $registry = new TokenParserRegistry([
            $parser1,
            $parser2,
            $parser3,
        ]);
        $actual = $registry->parse($token);

        self::assertSame($expected, $actual);

        $parser1Prophecy->canParse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $parser2Prophecy->canParse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $parser2Prophecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testThrowsAnExceptionIfNoSuitableParserIsFound(): void
    {
        $registry = new TokenParserRegistry([]);

        $this->expectException(ParserNotFoundException::class);
        $this->expectExceptionMessage('No suitable token parser found to handle the token "foo" (type: STRING_TYPE).');

        $registry->parse(new Token('foo', new TokenType(TokenType::STRING_TYPE)));
    }
}
