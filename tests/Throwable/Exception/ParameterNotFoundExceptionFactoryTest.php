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

use Nelmio\Alice\Parameter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\ParameterNotFoundExceptionFactory
 */
class ParameterNotFoundExceptionFactoryTest extends TestCase
{
    public function testIsARuntimeException(): void
    {
        static::assertTrue(is_a(ParameterNotFoundException::class, RuntimeException::class, true));
    }

    public function testCreateNewExceptionWithFactory(): void
    {
        $exception = ParameterNotFoundExceptionFactory::create('foo');

        static::assertEquals(
            'Could not find the parameter "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForWhenResolvingParameter(): void
    {
        $exception = ParameterNotFoundExceptionFactory::createForWhenResolvingParameter(
            'foo',
            new Parameter('bar', 'baz')
        );

        static::assertEquals(
            'Could not find the parameter "foo" when resolving "bar".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }
}
