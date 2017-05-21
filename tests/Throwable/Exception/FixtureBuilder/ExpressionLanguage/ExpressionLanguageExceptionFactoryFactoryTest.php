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

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ExpressionLanguageExceptionFactory
 */
class ExpressionLanguageExceptionFactoryTest extends TestCase
{
    public function testCreateForNoParserFoundForToken()
    {
        $token = new Token('foo', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $exception = ExpressionLanguageExceptionFactory::createForNoParserFoundForToken($token);

        $this->assertEquals(
            'No suitable token parser found to handle the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForExpectedMethodCallOnlyIfHasAParser()
    {
        $exception = ExpressionLanguageExceptionFactory::createForExpectedMethodCallOnlyIfHasAParser('foo');

        $this->assertEquals(
            'Expected method "foo" to be called only if it has a parser.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForUnparsableToken()
    {
        $token = new Token('foo', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $exception = ExpressionLanguageExceptionFactory::createForUnparsableToken($token);

        $this->assertEquals(
            'Could not parse the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();

        $exception = ExpressionLanguageExceptionFactory::createForUnparsableToken($token, $code, $previous);
        $this->assertEquals(
            'Could not parse the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForMalformedFunction()
    {
        $exception = ExpressionLanguageExceptionFactory::createForMalformedFunction('foo');

        $this->assertEquals(
            'The value "foo" contains an unclosed function.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCreateForCouldNotLexValue()
    {
        $exception = ExpressionLanguageExceptionFactory::createForCouldNotLexValue('foo');

        $this->assertEquals(
            'Could not lex the value "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
