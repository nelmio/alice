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
use RuntimeException;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(ParameterNotFoundException::class)]
final class ParameterNotFoundExceptionTest extends TestCase
{
    public function testIsARuntimeException(): void
    {
        self::assertTrue(is_a(ParameterNotFoundException::class, RuntimeException::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildParameterNotFoundException();
        self::assertInstanceOf(ChildParameterNotFoundException::class, $exception);
    }
}
