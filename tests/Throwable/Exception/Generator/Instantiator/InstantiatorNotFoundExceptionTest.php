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

namespace Nelmio\Alice\Throwable\Exception\Generator\Instantiator;

use LogicException;
use Nelmio\Alice\Throwable\InstantiationThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiatorNotFoundException
 * @internal
 */
class InstantiatorNotFoundExceptionTest extends TestCase
{
    public function testIsALogicException(): void
    {
        self::assertTrue(is_a(InstantiatorNotFoundException::class, LogicException::class, true));
    }

    public function testIsNotAnInstantiationThrowable(): void
    {
        self::assertFalse(is_a(InstantiatorNotFoundException::class, InstantiationThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildInstantiatorNotFoundException();
        self::assertInstanceOf(ChildInstantiatorNotFoundException::class, $exception);
    }
}
