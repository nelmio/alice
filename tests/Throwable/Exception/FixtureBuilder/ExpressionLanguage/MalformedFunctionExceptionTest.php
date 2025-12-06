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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage;

use InvalidArgumentException;
use Nelmio\Alice\Throwable\ExpressionLanguageParseThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(MalformedFunctionException::class)]
final class MalformedFunctionExceptionTest extends TestCase
{
    public function testIsAnInvalidArgumentException(): void
    {
        self::assertTrue(is_a(MalformedFunctionException::class, InvalidArgumentException::class, true));
    }

    public function testIsNotAParseThrowable(): void
    {
        self::assertFalse(is_a(MalformedFunctionException::class, ExpressionLanguageParseThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildMalformedFunctionException();
        self::assertInstanceOf(ChildMalformedFunctionException::class, $exception);
    }
}
