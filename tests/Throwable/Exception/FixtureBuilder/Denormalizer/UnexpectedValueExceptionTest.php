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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer;

use Nelmio\Alice\Throwable\DenormalizationThrowable;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(UnexpectedValueException::class)]
final class UnexpectedValueExceptionTest extends TestCase
{
    public function testIsARuntimeException(): void
    {
        self::assertTrue(is_a(UnexpectedValueException::class, RuntimeException::class, true));
    }

    public function testIsADenormalizationThrowable(): void
    {
        self::assertTrue(is_a(UnexpectedValueException::class, DenormalizationThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildUnexpectedValueException();
        self::assertInstanceOf(ChildUnexpectedValueException::class, $exception);
    }
}
