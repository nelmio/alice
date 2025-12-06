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

namespace Nelmio\Alice\Throwable\Exception\PropertyAccess;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(NoSuchPropertyExceptionFactory::class)]
final class NoSuchPropertyExceptionFactoryTest extends TestCase
{
    public function testCreateForUnreadablePropertyFromStdClass(): void
    {
        $exception = NoSuchPropertyExceptionFactory::createForUnreadablePropertyFromStdClass('foo');

        self::assertEquals(
            'Cannot read property "foo" from stdClass.',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
