<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    }
}
