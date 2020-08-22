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

use LogicException;
use Nelmio\Alice\Throwable\DenormalizationThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\DenormalizerNotFoundException
 */
class DenormalizerNotFoundExceptionTest extends TestCase
{
    public function testIsALogicException(): void
    {
        static::assertTrue(is_a(DenormalizerNotFoundException::class, LogicException::class, true));
    }

    public function testIsNotADenormalizationThrowable(): void
    {
        static::assertFalse(is_a(DenormalizerNotFoundException::class, DenormalizationThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildDenormalizerNotFoundException();
        static::assertInstanceOf(ChildDenormalizerNotFoundException::class, $exception);
    }
}
