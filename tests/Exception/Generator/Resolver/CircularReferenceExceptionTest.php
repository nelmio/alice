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
 * @covers \Nelmio\Alice\Exception\Generator\Resolver\CircularReferenceException
 */
class CircularReferenceExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(CircularReferenceException::class, \RuntimeException::class, true));
    }

    public function testIsAResolutionThrowable()
    {
        $this->assertTrue(is_a(CircularReferenceException::class, ResolutionThrowable::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = CircularReferenceException::createForParameter('foo', ['bar' => 1, 'baz' => 0]);

        $this->assertEquals(
            'Circular reference detected for the parameter "foo" while resolving ["bar", "baz"].',
            $exception->getMessage()
        );
    }
}
