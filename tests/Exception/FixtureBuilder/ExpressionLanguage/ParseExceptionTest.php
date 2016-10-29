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

namespace Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\ExpressionLanguageParseThrowable;

/**
 * @covers \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException
 */
class ParseExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAnException()
    {
        $this->assertTrue(is_a(ParseException::class, \Exception::class, true));
    }

    public function testIsAParseThrowable()
    {
        $this->assertTrue(is_a(ParseException::class, ExpressionLanguageParseThrowable::class, true));
    }

    public function testCanCreateExceptionWithTheFactory()
    {
        $token = new Token('foo', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $exception = ParseException::createForToken($token);
        
        $this->assertEquals(
            'Could not parse the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());

        
        $code = 500;
        $previous = new \Error('hello');

        $exception = ParseException::createForToken($token, $code, $previous);
        $this->assertEquals(
            'Could not parse the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $token = new Token('foo', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));

        $exception = ChildParseException::createForToken($token);
        $this->assertInstanceOf(ChildParseException::class, $exception);
    }
}

class ChildParseException extends ParseException
{
}
