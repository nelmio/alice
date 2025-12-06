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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\LogicExceptionFactory
 * @internal
 */
final class LogicExceptionFactoryTest extends TestCase
{
    public function testCreateForUncallableMethod(): void
    {
        $exception = LogicExceptionFactory::createForUncallableMethod('foo');

        self::assertEquals(
            'By its nature, "foo()" should not be called.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForCannotDenormalizerForChainableFixtureBuilderDenormalizer(): void
    {
        $exception = LogicExceptionFactory::createForCannotDenormalizerForChainableFixtureBuilderDenormalizer('foo');

        self::assertEquals(
            'As a chainable denormalizer, "foo" should be called only if "::canDenormalize() returns true. Got '
            .'false instead.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }

    public function testCreateForCannotHaveBothConstructorAndFactory(): void
    {
        $exception = LogicExceptionFactory::createForCannotHaveBothConstructorAndFactory();

        self::assertEquals(
            'Cannot use the fixture property "__construct" and "__factory" together.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
