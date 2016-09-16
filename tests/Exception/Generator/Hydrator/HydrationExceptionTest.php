<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Generator\Hydrator;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Throwable\HydrationThrowable;

/**
 * @covers \Nelmio\Alice\Exception\Generator\Hydrator\HydrationException
 */
class HydrationExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(HydrationException::class, \RuntimeException::class, true));
    }

    public function testIsAHydrationThrowable()
    {
        $this->assertTrue(is_a(HydrationException::class, HydrationThrowable::class, true));
    }

    public function testCreateExceptionViaFactory()
    {
        $object = new SimpleObject('dummy', new \stdClass());
        $property = new Property('foo', 'bar');

        $exception = HydrationException::create($object, $property);
        $this->assertEquals(
            'Could not hydrate the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage()
        );

        $code = 100;
        $previous = new \Error('hello');

        $exception = HydrationException::create($object, $property, $code, $previous);
        $this->assertEquals(
            'Could not hydrate the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $object = new SimpleObject('dummy', new \stdClass());
        $property = new Property('foo', 'bar');

        $exception = ChildHydrationException::create($object, $property);
        $this->assertInstanceOf(ChildHydrationException::class, $exception);

        $code = 100;
        $previous = new \Error('hello');

        $exception = ChildHydrationException::create($object, $property, $code, $previous);
        $this->assertInstanceOf(ChildHydrationException::class, $exception);
    }
}

class ChildHydrationException extends HydrationException
{
}
