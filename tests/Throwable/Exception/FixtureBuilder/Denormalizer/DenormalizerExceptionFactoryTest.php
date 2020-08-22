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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer;

use Error;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\DenormalizerExceptionFactory
 */
class DenormalizerExceptionFactoryTest extends TestCase
{
    public function testCreateForUndenormalizableConstructor(): void
    {
        $exception = DenormalizerExceptionFactory::createForUndenormalizableConstructor();
        static::assertEquals(
            'Could not denormalize the given constructor.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForUndenormalizableFactory(): void
    {
        $exception = DenormalizerExceptionFactory::createForUndenormalizableFactory();
        static::assertEquals(
            'Could not denormalize the given factory.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForUnparsableValue(): void
    {
        $code = 500;
        $previous = new Error();

        $exception = DenormalizerExceptionFactory::createForUnparsableValue('foo', $code, $previous);
        static::assertEquals(
            'Could not parse value "foo".',
            $exception->getMessage()
        );
        static::assertEquals($code, $exception->getCode());
        static::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateDenormalizerNotFoundForFixture(): void
    {
        $exception = DenormalizerExceptionFactory::createDenormalizerNotFoundForFixture('foo');

        static::assertEquals(
            'No suitable fixture denormalizer found to handle the fixture with the reference "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateDenormalizerNotFoundUnexpectedCall(): void
    {
        $exception = DenormalizerExceptionFactory::createDenormalizerNotFoundUnexpectedCall('fake');

        static::assertEquals(
            'Expected method "fake" to be called only if it has a denormalizer.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidScopeForUniqueValue(): void
    {
        $exception = DenormalizerExceptionFactory::createForInvalidScopeForUniqueValue();

        static::assertEquals(
            'Cannot bind a unique value scope to a temporary fixture.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }
}
