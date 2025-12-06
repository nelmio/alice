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

use Exception;
use Nelmio\Alice\Throwable\ExpressionLanguageParseThrowable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LexException::class)]
final class LexExceptionTest extends TestCase
{
    public function testIsAnException(): void
    {
        self::assertTrue(is_a(LexException::class, Exception::class, true));
    }

    public function testIsAParseThrowable(): void
    {
        self::assertTrue(is_a(LexException::class, ExpressionLanguageParseThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildLexException();
        self::assertInstanceOf(ChildLexException::class, $exception);
    }
}
