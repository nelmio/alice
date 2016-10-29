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

namespace Nelmio\Alice\Exception\Generator\Hydrator;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Throwable\HydrationThrowable;

/**
 * @covers \Nelmio\Alice\Exception\Generator\Hydrator\InvalidArgumentException
 */
class InvalidArgumentExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(InvalidArgumentException::class, \RuntimeException::class, true));
    }

    public function testIsAHydrationThrowable()
    {
        $this->assertTrue(is_a(InvalidArgumentException::class, HydrationThrowable::class, true));
    }

    public function testCreateExceptionViaFactory()
    {
        $object = new SimpleObject('dummy', new \stdClass());
        $property = new Property('foo', 'bar');

        $exception = InvalidArgumentException::create($object, $property);
        $this->assertEquals(
            'Invalid value given for the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 100;
        $previous = new \Error('hello');

        $exception = InvalidArgumentException::create($object, $property, $code, $previous);
        $this->assertEquals(
            'Invalid value given for the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $object = new SimpleObject('dummy', new \stdClass());
        $property = new Property('foo', 'bar');

        $exception = ChildInvalidArgumentException::create($object, $property);
        $this->assertInstanceOf(ChildInvalidArgumentException::class, $exception);
    }
}

class ChildInvalidArgumentException extends InvalidArgumentException
{
}
