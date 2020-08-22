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

use Nelmio\Alice\Definition\Value\DummyValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory
 */
class ResolverNotFoundExceptionFactoryTest extends TestCase
{
    public function testCreateNewExceptionWithFactoryForParameter(): void
    {
        $exception = ResolverNotFoundExceptionFactory::createForParameter('foo');

        static::assertEquals(
            'No resolver found to resolve parameter "foo".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateNewExceptionWithFactoryForValue(): void
    {
        $exception = ResolverNotFoundExceptionFactory::createForValue(new DummyValue('dummy'));

        static::assertEquals(
            'No resolver found to resolve value "dummy".',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }

    public function testCreateNewExceptionWithFactoryForUnexpectedCall(): void
    {
        $exception = ResolverNotFoundExceptionFactory::createUnexpectedCall('fake');

        static::assertEquals(
            'Expected method "fake" to be called only if it has a resolver.',
            $exception->getMessage()
        );
        static::assertEquals(0, $exception->getCode());
        static::assertNull($exception->getPrevious());
    }
}
