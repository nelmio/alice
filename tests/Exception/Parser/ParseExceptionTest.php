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
 * @covers Nelmio\Alice\Exception\Parser\ParseException
 */
class ParseExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAnException()
    {
        $this->assertTrue(is_a(ParseException::class, \Exception::class, true));
    }

    public function testIsAParseThrowable()
    {
        $this->assertTrue(is_a(ParseException::class, ParseThrowable::class, true));
    }
}
