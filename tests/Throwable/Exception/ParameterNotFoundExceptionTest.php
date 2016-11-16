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

/**
 * @covers \Nelmio\Alice\Throwable\Exception\ParameterNotFoundException
 */
class ParameterNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(ParameterNotFoundException::class, \RuntimeException::class, true));
    }

    public function testIsExtensible()
    {
        $exception = new ChildParameterNotFoundException();
        $this->assertInstanceOf(ChildParameterNotFoundException::class, $exception);
    }
}
