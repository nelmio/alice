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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
#[CoversClass(FixtureNotFoundException::class)]
final class FixtureNotFoundExceptionTest extends TestCase
{
    public function testIsARuntimeException(): void
    {
        self::assertTrue(is_a(FixtureNotFoundException::class, RuntimeException::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildFixtureNotFoundException();
        self::assertInstanceOf(ChildFixtureNotFoundException::class, $exception);
    }
}
