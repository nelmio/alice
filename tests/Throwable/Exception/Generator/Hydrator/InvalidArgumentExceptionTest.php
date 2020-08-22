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

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Throwable\HydrationThrowable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Hydrator\InvalidArgumentException
 */
class InvalidArgumentExceptionTest extends TestCase
{
    public function testIsARuntimeException(): void
    {
        static::assertTrue(is_a(InvalidArgumentException::class, RuntimeException::class, true));
    }

    public function testIsAHydrationThrowable(): void
    {
        static::assertTrue(is_a(InvalidArgumentException::class, HydrationThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $object = new SimpleObject('dummy', new stdClass());
        $property = new Property('foo', 'bar');

        $exception = new ChildInvalidArgumentException();
        static::assertInstanceOf(ChildInvalidArgumentException::class, $exception);
    }
}
