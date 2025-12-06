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

use LogicException;
use Nelmio\Alice\Throwable\ResolutionThrowable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ResolverNotFoundException::class)]
final class ResolverNotFoundExceptionTest extends TestCase
{
    public function testIsALogicException(): void
    {
        self::assertTrue(is_a(ResolverNotFoundException::class, LogicException::class, true));
    }

    public function testIsNotAResolutionThrowable(): void
    {
        self::assertFalse(is_a(ResolverNotFoundException::class, ResolutionThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildResolverNotFoundException();
        self::assertInstanceOf(ChildResolverNotFoundException::class, $exception);
    }
}
