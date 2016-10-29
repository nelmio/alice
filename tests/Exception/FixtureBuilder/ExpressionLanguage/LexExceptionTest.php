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
    }

    public function testCanCreateExceptionWithTheFactoryWithASpecificCode()
    {
        $exception = LexException::create('foo', 10);
        $this->assertEquals(
            'Could not lex the value "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(10, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testCanCreateExceptionWithTheFactoryAndAPreviousException()
    {
        $exception = LexException::create('foo', 10, $previous = new \Exception());
        $this->assertEquals(
            'Could not lex the value "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(10, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
