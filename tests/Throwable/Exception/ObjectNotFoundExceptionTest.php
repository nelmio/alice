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
#[CoversClass(ObjectNotFoundException::class)]
final class ObjectNotFoundExceptionTest extends TestCase
{
    public function testIsARuntimeException(): void
    {
        self::assertTrue(is_a(ObjectNotFoundException::class, RuntimeException::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildObjectNotFoundException();
        self::assertInstanceOf(ChildObjectNotFoundException::class, $exception);
    }
}
