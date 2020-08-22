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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage;

use Error;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory
 */
class ExpressionLanguageExceptionFactoryTest extends TestCase
{
    public function testCreateForNoParserFoundForToken(): void
    {
        $token = new Token('foo', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $exception = ExpressionLanguageExceptionFactory::createForNoParserFoundForToken($token);

        static::assertEquals(
            'No suitable token parser found to handle the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForExpectedMethodCallOnlyIfHasAParser(): void
    {
        $exception = ExpressionLanguageExceptionFactory::createForExpectedMethodCallOnlyIfHasAParser('foo');

        static::assertEquals(
            'Expected method "foo" to be called only if it has a parser.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForUnparsableToken(): void
    {
        $token = new Token('foo', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $exception = ExpressionLanguageExceptionFactory::createForUnparsableToken($token);

        static::assertEquals(
            'Could not parse the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());


        $code = 500;
        $previous = new Error();

        $exception = ExpressionLanguageExceptionFactory::createForUnparsableToken($token, $code, $previous);
        static::assertEquals(
            'Could not parse the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage()
        );
        static::assertEquals($code, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForMalformedFunction(): void
    {
        $exception = ExpressionLanguageExceptionFactory::createForMalformedFunction('foo');

        static::assertEquals(
            'The value "foo" contains an unclosed function.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForCouldNotLexValue(): void
    {
        $exception = ExpressionLanguageExceptionFactory::createForCouldNotLexValue('foo');

        static::assertEquals(
            'Could not lex the value "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }
}
