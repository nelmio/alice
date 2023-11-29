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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\CircularReferenceExceptionFactory
 * @internal
 */
class CircularReferenceExceptionFactoryTest extends TestCase
{
    public function testCreateNewExceptionWithFactory(): void
    {
        $exception = CircularReferenceExceptionFactory::createForParameter('foo', ['bar' => 1, 'baz' => 0]);

        self::assertEquals(
            'Circular reference detected for the parameter "foo" while resolving ["bar", "baz"].',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
