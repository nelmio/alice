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
use RuntimeException;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
 * @internal
 */
class InstantiationExceptionTest extends TestCase
{
    public function testIsARuntimeException(): void
    {
        self::assertTrue(is_a(InstantiationException::class, RuntimeException::class, true));
    }

    public function testIsAnInstantiationThrowable(): void
    {
        self::assertTrue(is_a(InstantiationException::class, InstantiationThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildInstantiationException();
        self::assertInstanceOf(ChildInstantiationException::class, $exception);
    }
}
