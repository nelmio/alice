<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Parser;

use Nelmio\Alice\Throwable\ParseThrowable;

/**
 * @covers \Nelmio\Alice\Exception\Parser\ParserNotFoundException
 */
class ParserNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsALogicException()
    {
        $this->assertTrue(is_a(ParserNotFoundException::class, \LogicException::class, true));
    }

    public function testIsNotAParseThrowable()
    {
        $this->assertFalse(is_a(ParserNotFoundException::class, ParseThrowable::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = ParserNotFoundException::create('foo');

        $this->assertEquals(
            'No suitable parser found for the file "foo".',
            $exception->getMessage()
        );
    }
}
