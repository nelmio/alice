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

namespace Nelmio\Alice\Throwable\Exception\Generator\Resolver;

use Nelmio\Alice\Throwable\ResolutionThrowable;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\NoSuchPropertyException
 */
class NoSuchPropertyExceptionTest extends TestCase
{
    public function testIsARuntimeException(): void
    {
        static::assertTrue(is_a(NoSuchPropertyException::class, RuntimeException::class, true));
    }

    public function testIsAnUnresolvableValueException(): void
    {
        static::assertTrue(is_a(NoSuchPropertyException::class, UnresolvableValueException::class, true));
    }

    public function testIsAResolutionThrowable(): void
    {
        static::assertTrue(is_a(NoSuchPropertyException::class, ResolutionThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildNoSuchPropertyException();
        static::assertInstanceOf(ChildNoSuchPropertyException::class, $exception);
    }
}
