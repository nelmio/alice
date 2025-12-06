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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExpressionLanguageExceptionFactory::class)]
final class ExpressionLanguageExceptionFactoryTest extends TestCase
{
    public function testCreateForNoParserFoundForToken(): void
    {
        $token = new Token('foo', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $exception = ExpressionLanguageExceptionFactory::createForNoParserFoundForToken($token);

        self::assertEquals(
            'No suitable token parser found to handle the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForExpectedMethodCallOnlyIfHasAParser(): void
    {
        $exception = ExpressionLanguageExceptionFactory::createForExpectedMethodCallOnlyIfHasAParser('foo');

        self::assertEquals(
            'Expected method "foo" to be called only if it has a parser.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForUnparsableToken(): void
    {
        $token = new Token('foo', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $exception = ExpressionLanguageExceptionFactory::createForUnparsableToken($token);

        self::assertEquals(
            'Could not parse the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());

        $code = 500;
        $previous = new Error();

        $exception = ExpressionLanguageExceptionFactory::createForUnparsableToken($token, $code, $previous);
        self::assertEquals(
            'Could not parse the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage(),
        );
        self::assertEquals($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForMalformedFunction(): void
    {
        $exception = ExpressionLanguageExceptionFactory::createForMalformedFunction('foo');

        self::assertEquals(
            'The value "foo" contains an unclosed function.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForCouldNotLexValue(): void
    {
        $exception = ExpressionLanguageExceptionFactory::createForCouldNotLexValue('foo');

        self::assertEquals(
            'Could not lex the value "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
