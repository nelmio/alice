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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
#[CoversClass(InvalidScopeException::class)]
final class InvalidScopeExceptionTest extends TestCase
{
    public function testIsARuntimeException(): void
    {
        self::assertTrue(is_a(InvalidScopeException::class, RuntimeException::class, true));
    }

    public function testIsADenormalizationThrowable(): void
    {
        self::assertTrue(is_a(InvalidScopeException::class, DenormalizationThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildInvalidScopeException();
        self::assertInstanceOf(ChildInvalidScopeException::class, $exception);
    }
}
