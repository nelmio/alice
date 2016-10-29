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

namespace Nelmio\Alice\Exception\Generator\Resolver;

use Nelmio\Alice\Throwable\ResolutionThrowable;

/**
 * @covers \Nelmio\Alice\Exception\Generator\Resolver\RecursionLimitReachedException
 */
class RecursionLimitReachedExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(RecursionLimitReachedException::class, \RuntimeException::class, true));
    }

    public function testIsAResolutionThrowable()
    {
        $this->assertTrue(is_a(RecursionLimitReachedException::class, ResolutionThrowable::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = RecursionLimitReachedException::create(10, 'foo');

        $this->assertEquals(
            'Recursion limit (10 tries) reached while resolving the parameter "foo"',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();
        $exception = RecursionLimitReachedException::create(10, 'foo', $code, $previous);

        $this->assertEquals(
            'Recursion limit (10 tries) reached while resolving the parameter "foo"',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildRecursionLimitReachedException::create(10, 'foo');
        $this->assertInstanceOf(ChildRecursionLimitReachedException::class, $exception);
    }
}

class ChildRecursionLimitReachedException extends RecursionLimitReachedException
{
}

