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

namespace Nelmio\Alice\Throwable\Exception\Generator\Instantiator;

use Nelmio\Alice\Throwable\InstantiationThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
 */
class InstantiationExceptionTest extends TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(InstantiationException::class, \RuntimeException::class, true));
    }

    public function testIsAnInstantiationThrowable()
    {
        $this->assertTrue(is_a(InstantiationException::class, InstantiationThrowable::class, true));
    }

    public function testIsExtensible()
    {
        $exception = new ChildInstantiationException();
        $this->assertInstanceOf(ChildInstantiationException::class, $exception);
    }
}
