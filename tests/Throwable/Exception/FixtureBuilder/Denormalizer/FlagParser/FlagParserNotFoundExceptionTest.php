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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser;

use LogicException;
use Nelmio\Alice\Throwable\DenormalizationThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserNotFoundException
 * @internal
 */
class FlagParserNotFoundExceptionTest extends TestCase
{
    public function testIsALogicException(): void
    {
        self::assertTrue(is_a(FlagParserNotFoundException::class, LogicException::class, true));
    }

    public function testIsNotADenormalizationThrowable(): void
    {
        self::assertFalse(is_a(FlagParserNotFoundException::class, DenormalizationThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildFlagParserNotFoundException();
        self::assertInstanceOf(ChildFlagParserNotFoundException::class, $exception);
    }
}
