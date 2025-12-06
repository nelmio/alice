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

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FakeParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParserNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 */
#[CoversClass(AbstractChainableParserAwareParser::class)]
final class AbstractChainableParserAwareParserTest extends TestCase
{
    public function testIsAChainableTokenParser(): void
    {
        self::assertTrue(is_a(ImpartialChainableParserAwareParser::class, AbstractChainableParserAwareParser::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(ImpartialChainableParserAwareParser::class))->isCloneable());
    }

    public function testCanBeInstantiatedWithoutAParser(): void
    {
        new ImpartialChainableParserAwareParser();
    }

    public function testCanBeInstantiatedWithAParser(): void
    {
        new ImpartialChainableParserAwareParser(new FakeParser());
    }

    public function testWithersReturnNewAModifiedInstance(): void
    {
        $parser = new ImpartialChainableParserAwareParser();
        $newParser = $parser->withParser(new FakeParser());

        self::assertEquals(new ImpartialChainableParserAwareParser(), $parser);
        self::assertEquals(new ImpartialChainableParserAwareParser(new FakeParser()), $newParser);
    }

    public function testThrowsAnExceptionIfNoDecoratedParserIsFound(): void
    {
        $token = new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $parser = new ImpartialChainableParserAwareParser();

        $this->expectException(ParserNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.');

        $parser->parse($token);
    }

    public function testDoNothingIfTriesToParseATokenAndDecoratedParserIsFound(): void
    {
        $token = new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $parser = new ImpartialChainableParserAwareParser(new FakeParser());

        self::assertNull($parser->parse($token));
    }
}
