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
 * @internal
 */
class DenormalizerExceptionFactoryTest extends TestCase
{
    public function testCreateForUndenormalizableConstructor(): void
    {
        $exception = DenormalizerExceptionFactory::createForUndenormalizableConstructor();
        self::assertEquals(
            'Could not denormalize the given constructor.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForUndenormalizableFactory(): void
    {
        $exception = DenormalizerExceptionFactory::createForUndenormalizableFactory();
        self::assertEquals(
            'Could not denormalize the given factory.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForUnparsableValue(): void
    {
        $code = 500;
        $previous = new Error();

        $exception = DenormalizerExceptionFactory::createForUnparsableValue('foo', $code, $previous);
        self::assertEquals(
            'Could not parse value "foo".',
            $exception->getMessage(),
        );
        self::assertEquals($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateDenormalizerNotFoundForFixture(): void
    {
        $exception = DenormalizerExceptionFactory::createDenormalizerNotFoundForFixture('foo');

        self::assertEquals(
            'No suitable fixture denormalizer found to handle the fixture with the reference "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateDenormalizerNotFoundUnexpectedCall(): void
    {
        $exception = DenormalizerExceptionFactory::createDenormalizerNotFoundUnexpectedCall('fake');

        self::assertEquals(
            'Expected method "fake" to be called only if it has a denormalizer.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForInvalidScopeForUniqueValue(): void
    {
        $exception = DenormalizerExceptionFactory::createForInvalidScopeForUniqueValue();

        self::assertEquals(
            'Cannot bind a unique value scope to a temporary fixture.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
