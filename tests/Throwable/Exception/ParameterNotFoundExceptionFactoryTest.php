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
 * @internal
 */
final class ParameterNotFoundExceptionFactoryTest extends TestCase
{
    public function testIsARuntimeException(): void
    {
        self::assertTrue(is_a(ParameterNotFoundException::class, RuntimeException::class, true));
    }

    public function testCreateNewExceptionWithFactory(): void
    {
        $exception = ParameterNotFoundExceptionFactory::create('foo');

        self::assertEquals(
            'Could not find the parameter "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForWhenResolvingParameter(): void
    {
        $exception = ParameterNotFoundExceptionFactory::createForWhenResolvingParameter(
            'foo',
            new Parameter('bar', 'baz'),
        );

        self::assertEquals(
            'Could not find the parameter "foo" when resolving "bar".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
