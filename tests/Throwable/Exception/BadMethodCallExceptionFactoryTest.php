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
 * @covers \Nelmio\Alice\Throwable\Exception\BadMethodCallExceptionFactory
 * @internal
 */
class BadMethodCallExceptionFactoryTest extends TestCase
{
    public function testCreateForUnknownMethod(): void
    {
        $exception = BadMethodCallExceptionFactory::createForUnknownMethod('foo');

        self::assertEquals(
            'Unknown method "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
