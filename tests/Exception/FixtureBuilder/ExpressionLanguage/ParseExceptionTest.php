<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType;
use Nelmio\Alice\Throwable\ExpressionLanguageParseThrowable;

/**
 * @covers Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParseException
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
    }

    public function testCanCreateExceptionWithTheFactoryWithASpecificCode()
    {
        $token = new Token('foo', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $exception = ParseException::createForToken($token, 10);
        $this->assertEquals(
            'Could not parse the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage()
        );
        $this->assertEquals(10, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCanCreateExceptionWithTheFactoryAndAPreviousException()
    {
        $token = new Token('foo', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $exception = ParseException::createForToken($token, 10, $previous = new \Exception());
        $this->assertEquals(
            'Could not parse the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage()
        );
        $this->assertEquals(10, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
