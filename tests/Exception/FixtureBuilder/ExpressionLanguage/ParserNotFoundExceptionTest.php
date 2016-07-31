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
 * @covers Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\ParserNotFoundException
 */
class ParserNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsALogicException()
    {
        $this->assertTrue(is_a(ParserNotFoundException::class, \LogicException::class, true));
    }

    public function testIsNotAParseThrowable()
    {
        $this->assertFalse(is_a(ParserNotFoundException::class, ExpressionLanguageParseThrowable::class, true));
    }

    public function testTestCreateNewExceptionForTokenWithTheFactory()
    {
        $token = new Token('foo', new TokenType(TokenType::DYNAMIC_ARRAY_TYPE));
        $exception = ParserNotFoundException::create($token);

        $this->assertEquals(
            'No suitable token parser found to handle the token "foo" (type: DYNAMIC_ARRAY_TYPE).',
            $exception->getMessage()
        );
    }

    public function testTestCreateNewExceptionForUnexpectedCallWithTheFactory()
    {
        $exception = ParserNotFoundException::createUnexpectedCall('foo');

        $this->assertEquals(
            'Expected method "foo" to be called only if it has a parser.',
            $exception->getMessage()
        );
    }
}
