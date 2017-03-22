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

namespace Nelmio\Alice\Throwable\Exception;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\NoValueForCurrentException
 */
class NoValueForCurrentExceptionTest extends TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(NoValueForCurrentException::class, \RuntimeException::class, true));
    }

    public function testIsExtensible()
    {
        $exception = new ChildNoValueForCurrentException();
        $this->assertInstanceOf(ChildNoValueForCurrentException::class, $exception);
    }
}
