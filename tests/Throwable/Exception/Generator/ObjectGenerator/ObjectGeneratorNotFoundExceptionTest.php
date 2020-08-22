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

namespace Nelmio\Alice\Throwable\Exception\Generator\ObjectGenerator;

use LogicException;
use Nelmio\Alice\Throwable\GenerationThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\ObjectGenerator\ObjectGeneratorNotFoundException
 */
class ObjectGeneratorNotFoundExceptionTest extends TestCase
{
    public function testIsALogicException(): void
    {
        static::assertTrue(is_a(ObjectGeneratorNotFoundException::class, LogicException::class, true));
    }

    public function testIsNotAGenerationThrowable(): void
    {
        static::assertFalse(is_a(ObjectGeneratorNotFoundException::class, GenerationThrowable::class, true));
    }

    public function testIsExtensible(): void
    {
        $exception = new ChildObjectGeneratorNotFoundException();
        static::assertInstanceOf(ChildObjectGeneratorNotFoundException::class, $exception);
    }
}
