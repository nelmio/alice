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

use Closure;
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
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\FixtureMethodReferenceTokenParser
 * @internal
 */
class FixtureMethodReferenceTokenParserTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableTokenParser(): void
    {
        self::assertTrue(is_a(FixtureMethodReferenceTokenParser::class, ChainableTokenParserInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(FixtureMethodReferenceTokenParser::class))->isCloneable());
    }

    public function testCanParseMethodReferenceTokens(): void
    {
        $token = new Token('', new TokenType(TokenType::METHOD_REFERENCE_TYPE));
        $anotherToken = new Token('', new TokenType(TokenType::IDENTITY_TYPE));
        $parser = new FixtureMethodReferenceTokenParser();

        self::assertTrue($parser->canParse($token));
        self::assertFalse($parser->canParse($anotherToken));
    }

    public function testThrowsAnExceptionIfNoDecoratedParserIsFound(): void
    {
        $token = new Token('', new TokenType(TokenType::METHOD_REFERENCE_TYPE));
        $parser = new FixtureMethodReferenceTokenParser();

        $this->expectException(ParserNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\TokenParser\Chainable\AbstractChainableParserAwareParser::parse" to be called only if it has a parser.');

        $parser->parse($token);
    }

    public function testThrowsAnExceptionIfCouldNotParseToken(): void
    {
        $token = new Token('', new TokenType(TokenType::METHOD_REFERENCE_TYPE));
        $parser = new FixtureMethodReferenceTokenParser(new FakeParser());

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "" (type: METHOD_REFERENCE_TYPE).');

        $parser->parse($token);
    }

    public function testReturnsFunctionValue(): void
    {
        $token = new Token('@user->getName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE));

        $decoratedParserProphecy = $this->prophesize(ParserInterface::class);
        $decoratedParserProphecy->parse('@user')->willReturn($reference = new FixtureReferenceValue('user'));
        $decoratedParserProphecy->parse('<getName()>')->willReturn($call = new FunctionCallValue('getName'));
        /** @var ParserInterface $decoratedParser */
        $decoratedParser = $decoratedParserProphecy->reveal();

        $expected = new FixtureMethodCallValue($reference, $call);

        $parser = new FixtureMethodReferenceTokenParser($decoratedParser);
        $actual = $parser->parse($token);

        self::assertEquals($expected, $actual);
    }

    public function testThrowsAnExceptionIfMethodReferenceIsMalformed(): void
    {
        $token = new Token('@user->getName()->anotherName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE));

        $parser = new FixtureMethodReferenceTokenParser(new FakeParser());

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Could not parse the token "@user->getName()->anotherName()" (type: METHOD_REFERENCE_TYPE).');

        $parser->parse($token);
    }

    /**
     * @dataProvider provideParser
     *
     * @param Closure(self): ParserInterface $decoratedParserFactory
     */
    public function testThrowsAnExceptionIfParsingReturnsAnUnexpectedResult(Closure $decoratedParserFactory): void
    {
        $decoratedParser = $decoratedParserFactory($this);

        try {
            $token = new Token('@user->getName()', new TokenType(TokenType::METHOD_REFERENCE_TYPE));

            $parser = new FixtureMethodReferenceTokenParser($decoratedParser);
            $parser->parse($token);
            self::fail('Expected exception to be thrown.');
        } catch (ParseException $exception) {
            self::assertEquals(
                'Could not parse the token "@user->getName()" (type: METHOD_REFERENCE_TYPE).',
                $exception->getMessage(),
            );
            self::assertEquals(0, $exception->getCode());
            self::assertNotNull($exception->getPrevious());
        }
    }

    public static function provideParser(): iterable
    {
        yield 'unexpected reference' => [
            function (self $testCase) {
                $decoratedParserProphecy = $testCase->prophesize(ParserInterface::class);
                $decoratedParserProphecy->parse('@user')->willReturn('foo');
                $decoratedParserProphecy->parse('<getName()>')->willReturn(new FunctionCallValue('getName'));

                return $decoratedParserProphecy->reveal();
            },
        ];

        yield 'unexpected fixture call' => [
            function (self $testCase) {
                $decoratedParserProphecy = $testCase->prophesize(ParserInterface::class);
                $decoratedParserProphecy->parse('@user')->willReturn(new FixtureReferenceValue('user'));
                $decoratedParserProphecy->parse('<getName()>')->willReturn('foo');

                return $decoratedParserProphecy->reveal();
            },
        ];

        yield 'unexpected reference and fixture call' => [
            function (self $testCase) {
                $decoratedParserProphecy = $testCase->prophesize(ParserInterface::class);
                $decoratedParserProphecy->parse('@user')->willReturn('foo');
                $decoratedParserProphecy->parse('<getName()>')->willReturn('bar');

                return $decoratedParserProphecy->reveal();
            },
        ];
    }
}
