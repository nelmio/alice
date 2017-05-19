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

namespace Nelmio\Alice\Throwable\Exception\Parser;

use Nelmio\Alice\Throwable\ParseThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Parser\UnparsableFileException
 */
class UnparsableFileExceptionTest extends TestCase
{
    public function testIsAnException()
    {
        $this->assertTrue(is_a(UnparsableFileException::class, \Exception::class, true));
    }

    public function testIsAParseThrowable()
    {
        $this->assertTrue(is_a(UnparsableFileException::class, ParseThrowable::class, true));
    }

    public function testIsExtensible()
    {
        $exception = new ChildUnparsableFileException();
        $this->assertInstanceOf(ChildUnparsableFileException::class, $exception);
    }
}
