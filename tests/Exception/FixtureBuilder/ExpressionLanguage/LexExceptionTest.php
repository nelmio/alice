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

use Nelmio\Alice\Throwable\ExpressionLanguageParseThrowable;

/**
 * @covers \Nelmio\Alice\Exception\FixtureBuilder\ExpressionLanguage\LexException
 */
class LexExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAnException()
    {
        $this->assertTrue(is_a(LexException::class, \Exception::class, true));
    }

    public function testIsAParseThrowable()
    {
        $this->assertTrue(is_a(LexException::class, ExpressionLanguageParseThrowable::class, true));
    }

    public function testCanCreateExceptionWithTheFactory()
    {
        $exception = LexException::create('foo');
        $this->assertEquals(
            'Could not lex the value "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error('hello');

        $exception = LexException::create('foo', $code, $previous);
        $this->assertEquals(
            'Could not lex the value "foo".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildLexException::create('foo');
        $this->assertInstanceOf(ChildLexException::class, $exception);
    }
}

class ChildLexException extends LexException
{
}
