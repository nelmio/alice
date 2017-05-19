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

namespace Nelmio\Alice\Throwable\Exception\Generator\Caller;

use Nelmio\Alice\Throwable\Exception\Generator\Instantiator\ChildInstantiatorNotFoundException;
use Nelmio\Alice\Throwable\InstantiationThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Caller\ProcessorNotFoundException
 */
class ProcessorNotFoundExceptionTest extends TestCase
{
    public function testIsALogicException()
    {
        $this->assertTrue(is_a(ProcessorNotFoundException::class, \LogicException::class, true));
    }

    public function testIsNotAnInstantiationThrowable()
    {
        $this->assertFalse(is_a(ProcessorNotFoundException::class, InstantiationThrowable::class, true));
    }

    public function testIsExtensible()
    {
        $exception = new ChildInstantiatorNotFoundException();
        $this->assertInstanceOf(ChildInstantiatorNotFoundException::class, $exception);
    }
}
