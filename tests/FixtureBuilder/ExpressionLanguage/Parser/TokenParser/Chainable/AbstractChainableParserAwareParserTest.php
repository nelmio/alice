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
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser
 */
class AbstractChainableParserAwareParserTest extends TestCase
{
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(ImpartialChainableParserAwareParser::class, AbstractChainableParserAwareParser::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ImpartialChainableParserAwareParser::class))->isCloneable());
    }

    public function testCanBeInstantiatedWithoutAParser()
    {
        new ImpartialChainableParserAwareParser();
    }

    public function testCanBeInstantiatedWithAParser()
    {
        new ImpartialChainableParserAwareParser(new FakeParser());
    }

    public function testWithersReturnNewAModifiedInstance()
    {
        $parser = new ImpartialChainableParserAwareParser();
        $newParser = $parser->withParser(new FakeParser());

        $this->assertEquals(new ImpartialChainableParserAwareParser(), $parser);
        $this->assertEquals(new ImpartialChainableParserAwareParser(new FakeParser()), $newParser);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParserNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.
     */
    public function testThrowsAnExceptionIfNoDecoratedParserIsFound()
    {
        $token = new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $parser = new ImpartialChainableParserAwareParser();

        $parser->parse($token);
    }

    public function testDoNothingIfTriesToParseATokenAndDecoratedParserIsFound()
    {
        $token = new Token('', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $parser = new ImpartialChainableParserAwareParser(new FakeParser());

        $this->assertNull($parser->parse($token));
    }
}
