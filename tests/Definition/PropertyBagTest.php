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

namespace Nelmio\Alice\Definition;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * @covers \Nelmio\Alice\Definition\PropertyBag
 * @internal
 */
final class PropertyBagTest extends TestCase
{
    /**
     * @var ReflectionProperty
     */
    private $propRefl;

    protected function setUp(): void
    {
        $refl = new ReflectionClass(PropertyBag::class);
        $propRefl = $refl->getProperty('properties');

        $this->propRefl = $propRefl;
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $property = new Property('username', 'alice');

        $bag = new PropertyBag();
        $newBag = $bag->with($property);

        self::assertInstanceOf(PropertyBag::class, $newBag);
        self::assertSame([], $this->propRefl->getValue($bag));
        self::assertSame(['username' => $property], $this->propRefl->getValue($newBag));
    }

    /**
     * @testdox Can merge two bags. When properties overlaps, the existing ones are kept.
     */
    public function testMergeTwoBags(): void
    {
        $propertyA1 = new Property('username', 'alice');
        $propertyA2 = new Property('owner', 'bob');

        $propertyB1 = new Property('username', 'mad');  // overlapping value
        $propertyB2 = new Property('mail', 'bob@ex.com');

        $bagA = (new PropertyBag())
            ->with($propertyA1)
            ->with($propertyA2);
        $bagB = (new PropertyBag())
            ->with($propertyB1)
            ->with($propertyB2);

        $bag = $bagA->mergeWith($bagB);

        self::assertInstanceOf(PropertyBag::class, $bag);
        self::assertSame(
            [
                'username' => $propertyA1,
                'owner' => $propertyA2,
            ],
            $this->propRefl->getValue($bagA),
        );
        self::assertSame(
            [
                'username' => $propertyB1,
                'mail' => $propertyB2,
            ],
            $this->propRefl->getValue($bagB),
        );
        self::assertSame(
            [
                'username' => $propertyA1,
                'mail' => $propertyB2,
                'owner' => $propertyA2,
            ],
            $this->propRefl->getValue($bag),
        );
    }

    public function testIsIterable(): void
    {
        $property1 = new Property('username', 'alice');
        $property2 = new Property('owner', 'bob');

        $bag = (new PropertyBag())
            ->with($property1)
            ->with($property2);

        $array = [];
        foreach ($bag as $index => $property) {
            $array[$index] = $property;
        }

        self::assertSame(
            [
                $property1,
                $property2,
            ],
            $array,
        );
    }

    public function testIsCountable(): void
    {
        $bag = new PropertyBag();
        self::assertCount(0, $bag);

        $bag = $bag->with(new Property('foo', 'bar'));
        self::assertCount(1, $bag);

        $bag = $bag->with(new Property('foo', 'baz'));
        self::assertCount(1, $bag);

        $bag = $bag->with(new Property('ping', 'pong'));
        self::assertCount(2, $bag);
    }

    public function testIsEmpty(): void
    {
        $bag = new PropertyBag();
        self::assertTrue($bag->isEmpty());

        $bag = $bag->with(new Property('foo', null));
        self::assertFalse($bag->isEmpty());
    }
}
