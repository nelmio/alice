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
 * @covers \Nelmio\Alice\Exception\Generator\Hydrator\PropertyAccessException
 */
class PropertyAccessExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(PropertyAccessException::class, \RuntimeException::class, true));
    }

    public function testIsAHydrationThrowable()
    {
        $this->assertTrue(is_a(PropertyAccessException::class, HydrationThrowable::class, true));
    }

    public function testCreateExceptionViaFactory()
    {
        $object = new SimpleObject('dummy', new \stdClass());
        $property = new Property('foo', 'bar');

        $exception = PropertyAccessException::create($object, $property);
        $this->assertEquals(
            'Could not access to the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage()
        );

        $code = 100;
        $previous = new \Error('hello');

        $exception = PropertyAccessException::create($object, $property, $code, $previous);
        $this->assertEquals(
            'Could not access to the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $object = new SimpleObject('dummy', new \stdClass());
        $property = new Property('foo', 'bar');

        $exception = ChildPropertyAccessException::create($object, $property);
        $this->assertInstanceOf(ChildPropertyAccessException::class, $exception);

        $code = 100;
        $previous = new \Error('hello');

        $exception = ChildPropertyAccessException::create($object, $property, $code, $previous);
        $this->assertInstanceOf(ChildPropertyAccessException::class, $exception);
    }
}

class ChildPropertyAccessException extends PropertyAccessException
{
}
