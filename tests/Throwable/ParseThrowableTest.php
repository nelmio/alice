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

namespace Nelmio\Alice\Throwable;

use Nelmio\Alice\Throwable\Exception\RootParseException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
final class ParseThrowableTest extends TestCase
{
    public function testIsABuildThrowable(): void
    {
        self::assertTrue(is_a(RootParseException::class, LoadingThrowable::class, true));
    }
}
