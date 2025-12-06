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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FixtureNotFoundExceptionFactory::class)]
final class FixtureNotFoundExceptionFactoryTest extends TestCase
{
    public function testCreateNewExceptionWithFactory(): void
    {
        $exception = FixtureNotFoundExceptionFactory::create('foo');

        self::assertEquals(
            'Could not find the fixture "foo".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
