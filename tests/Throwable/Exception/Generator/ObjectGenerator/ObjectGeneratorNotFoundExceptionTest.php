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

use Nelmio\Alice\Throwable\GenerationThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\ObjectGenerator\ObjectGeneratorNotFoundException
 */
class ObjectGeneratorNotFoundExceptionTest extends TestCase
{
    public function testIsALogicException()
    {
        $this->assertTrue(is_a(ObjectGeneratorNotFoundException::class, \LogicException::class, true));
    }

    public function testIsNotAGenerationThrowable()
    {
        $this->assertFalse(is_a(ObjectGeneratorNotFoundException::class, GenerationThrowable::class, true));
    }

    public function testIsExtensible()
    {
        $exception = new ChildObjectGeneratorNotFoundException();
        $this->assertInstanceOf(ChildObjectGeneratorNotFoundException::class, $exception);
    }
}
