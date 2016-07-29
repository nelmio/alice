<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage\Parser;

use Nelmio\Alice\ExpressionLanguage\ParserAwareInterface;
use Nelmio\Alice\ExpressionLanguage\Token;
use Nelmio\Alice\ExpressionLanguage\TokenType;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\ExpressionLanguage\Parser\TokenParserRegistry
 */
class TokenParserRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $parsersRefl;

    public function setUp()
    {
        $this->parsersRefl = (new \ReflectionClass(TokenParserRegistry::class))->getProperty('parsers');
        $this->parsersRefl->setAccessible(true);
    }

    public function testIsAParser()
    {
        $this->assertTrue(is_a(TokenParserRegistry::class, TokenParserInterface::class, true));
    }

    public function testAcceptChainableParsers()
    {
        new TokenParserRegistry([new FakeChainableTokenParser()]);

        try {
            new TokenParserRegistry([new \stdClass()]);
            $this->fails('Expected exception to be thrown.');
        } catch (\TypeError $exception) {
            // expected
        }
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $parser = new FakeParser();

        $parserAwareProphecy = $this->prophesize(ParserAwareInterface::class);
        $parserAwareProphecy->withParser($parser)->willReturn(new \stdClass());
        /** @var ParserAwareInterface $parserAware */
        $parserAware = $parserAwareProphecy->reveal();

        $tokenParser = new TokenParserRegistry([
            $parser1 = new FakeChainableTokenParser(),
            $parser2 = new DummyChainableTokenParserAware(new FakeChainableTokenParser(), $parserAware)
        ]);

        $newTokenParser = $tokenParser->withParser($parser);

        $this->assertInstanceOf(TokenParserRegistry::class, $newTokenParser);

        $this->assertSame(
            [
                $parser1,
                $parser2,
            ],
            $this->parsersRefl->getValue($tokenParser)
        );

        $newTokenParserParsers = $this->parsersRefl->getValue($newTokenParser);
        $this->assertCount(2, $newTokenParserParsers);
        $this->assertEquals(
            [
                $parser1,
                new \stdClass(),
            ],
            $newTokenParserParsers
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testThrowExceptionIfInvalidParserIsPassed()
    {
        new TokenParserRegistry([new \stdClass()]);
    }

    public function testIsDeepClonable()
    {
        $parser = new TokenParserRegistry([]);
        $clone = clone $parser;

        $this->assertEquals($parser, $clone);
        $this->assertNotSame($parser, $clone);

        $parsers = [new FakeChainableTokenParser()];
        $parser = new TokenParserRegistry($parsers);
        $clone = clone $parser;

        $this->assertEquals($parser, $clone);
        $this->assertNotSame($parser, $clone);

        $this->assertSame($parsers, $this->parsersRefl->getValue($parser));
        $this->assertNotSame($parsers, $this->parsersRefl->getValue($clone));
    }

    public function testIterateOverEveryParsersAndUseTheFirstValidOne()
    {
        $token = new Token('foo', new TokenType(TokenType::STRING_TYPE));
        $expected = 'foo';

        $parser1Prophecy = $this->prophesize(ChainableTokenParserInterface::class);
        $parser1Prophecy->canParse($token)->willReturn(false);
        /* @var ChainableTokenParserInterface $parser1 */
        $parser1 = $parser1Prophecy->reveal();

        $parser2Prophecy = $this->prophesize(ChainableTokenParserInterface::class);
        $parser2Prophecy->canParse($token)->willReturn(true);
        $parser2Prophecy->parse($token)->willReturn($expected);
        /* @var ChainableTokenParserInterface $parser2 */
        $parser2 = $parser2Prophecy->reveal();

        $parser3Prophecy = $this->prophesize(ChainableTokenParserInterface::class);
        $parser3Prophecy->canParse(Argument::any())->shouldNotBeCalled();
        /* @var ChainableTokenParserInterface $parser3 */
        $parser3 = $parser3Prophecy->reveal();

        $registry = new TokenParserRegistry([
            $parser1,
            $parser2,
            $parser3,
        ]);
        $actual = $registry->parse($token);

        $this->assertSame($expected, $actual);

        $parser1Prophecy->canParse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $parser2Prophecy->canParse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $parser2Prophecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\ExpressionLanguage\ParserNotFoundException
     * @expectedExceptionMessage No suitable token parser found to handle the token "(STRING_TYPE) foo".
     */
    public function testThrowExceptionIfNoSuitableParserIsFound()
    {
        $registry = new TokenParserRegistry([]);
        $registry->parse(new Token('foo', new TokenType(TokenType::STRING_TYPE)));
    }
}
