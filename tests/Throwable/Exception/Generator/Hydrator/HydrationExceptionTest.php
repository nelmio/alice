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

namespace Nelmio\Alice\Throwable\Exception\Generator\Hydrator;

use Nelmio\Alice\Throwable\HydrationThrowable;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Hydrator\HydrationException
 * @internal
 */
class HydrationExceptionTest extends TestCase
{
    public function testIsARuntimeException(): void
    {
        self::assertTrue(is_a(HydrationException::class, RuntimeException::class, true));
    }

    public function testIsAHydrationThrowable(): void
    {
        self::assertTrue(is_a(HydrationException::class, HydrationThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildHydrationException();
        self::assertInstanceOf(ChildHydrationException::class, $exception);
    }
}
