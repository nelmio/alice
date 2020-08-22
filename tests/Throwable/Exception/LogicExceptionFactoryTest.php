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
 */
class LogicExceptionFactoryTest extends TestCase
{
    public function testCreateForUncallableMethod(): void
    {
        $exception = LogicExceptionFactory::createForUncallableMethod('foo');

        static::assertEquals(
            'By its nature, "foo()" should not be called.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForCannotDenormalizerForChainableFixtureBuilderDenormalizer(): void
    {
        $exception = LogicExceptionFactory::createForCannotDenormalizerForChainableFixtureBuilderDenormalizer('foo');

        static::assertEquals(
            'As a chainable denormalizer, "foo" should be called only if "::canDenormalize() returns true. Got '
            .'false instead.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateForCannotHaveBothConstructorAndFactory(): void
    {
        $exception = LogicExceptionFactory::createForCannotHaveBothConstructorAndFactory();

        static::assertEquals(
            'Cannot use the fixture property "__construct" and "__factory" together.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }
}
