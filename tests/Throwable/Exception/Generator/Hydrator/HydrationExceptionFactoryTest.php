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

namespace Nelmio\Alice\Throwable\Exception\Generator\Hydrator;

use Error;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Hydrator\HydrationExceptionFactory
 * @internal
 */
class HydrationExceptionFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $object = new SimpleObject('dummy', new stdClass());
        $property = new Property('foo', 'bar');

        $code = 500;
        $previous = new Error();

        $exception = HydrationExceptionFactory::create($object, $property, $code, $previous);
        self::assertEquals(
            'Could not hydrate the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage(),
        );
        self::assertEquals($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForInaccessibleProperty(): void
    {
        $object = new SimpleObject('dummy', new stdClass());
        $property = new Property('foo', 'bar');

        $exception = HydrationExceptionFactory::createForInaccessibleProperty($object, $property);
        self::assertEquals(
            'Could not access to the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());

        $code = 500;
        $previous = new Error();

        $exception = HydrationExceptionFactory::createForInaccessibleProperty($object, $property, $code, $previous);
        self::assertEquals(
            'Could not access to the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage(),
        );
        self::assertEquals($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForInvalidProperty(): void
    {
        $object = new SimpleObject('dummy', new stdClass());
        $property = new Property('foo', 'bar');

        $exception = HydrationExceptionFactory::createForInvalidProperty($object, $property);
        self::assertEquals(
            'Invalid value given for the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());

        $code = 500;
        $previous = new Error();

        $exception = HydrationExceptionFactory::createForInvalidProperty($object, $property, $code, $previous);
        self::assertEquals(
            'Invalid value given for the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage(),
        );
        self::assertEquals($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testCreateForCouldNotHydrateObjectWithProperty(): void
    {
        $object = new SimpleObject('dummy', new stdClass());
        $property = new Property('foo', 'bar');

        $exception = HydrationExceptionFactory::createForCouldNotHydrateObjectWithProperty($object, $property);
        self::assertEquals(
            'Could not hydrate the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());

        $code = 500;
        $previous = new Error();

        $exception = HydrationExceptionFactory::createForCouldNotHydrateObjectWithProperty($object, $property, $code, $previous);
        self::assertEquals(
            'Could not hydrate the property "foo" of the object "dummy" (class: stdClass).',
            $exception->getMessage(),
        );
        self::assertEquals($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
