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
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\IdentityTokenParser
 */
class IdentityTokenParserTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableTokenParser()
    {
        $this->assertTrue(is_a(IdentityTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new IdentityTokenParser(new FakeChainableTokenParser());
    }

    public function testCanParseIdentityTokens()
    {
        $token = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::ESCAPED_VALUE_TYPE));
        $parser = new IdentityTokenParser(new FakeChainableTokenParser());

        $this->assertTrue($parser->canParse($token));
        $this->assertFalse($parser->canParse($anotherToken));
    }

    public function testReplaceIdentityIntoAFunctionCallBeforeHandingItOverToItsDecorated()
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

        $this->assertEquals($expected, $actual);

        $decoratedParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
