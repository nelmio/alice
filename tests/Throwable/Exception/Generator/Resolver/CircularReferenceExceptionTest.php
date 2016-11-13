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

namespace Nelmio\Alice\Throwable\Exception\Generator\Resolver;

use Nelmio\Alice\Throwable\ResolutionThrowable;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\CircularReferenceException
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
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();
        $exception = CircularReferenceException::createForParameter('foo', ['bar' => 1, 'baz' => 0], $code, $previous);

        $this->assertEquals(
            'Circular reference detected for the parameter "foo" while resolving ["bar", "baz"].',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildCircularReferenceException::createForParameter('foo', ['bar' => 1, 'baz' => 0]);
        $this->assertInstanceOf(ChildCircularReferenceException::class, $exception);
    }
}

class ChildCircularReferenceException extends CircularReferenceException
{
}

