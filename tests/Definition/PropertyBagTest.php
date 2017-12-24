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

/**
 * @covers \Nelmio\Alice\Definition\PropertyBag
 */
class PropertyBagTest extends TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $propRefl;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $refl = new \ReflectionClass(PropertyBag::class);
        $propRefl = $refl->getProperty('properties');
        $propRefl->setAccessible(true);

        $this->propRefl = $propRefl;
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $property = new Property('username', 'alice');

        $bag = new PropertyBag();
        $newBag = $bag->with($property);

        $this->assertInstanceOf(PropertyBag::class, $newBag);
        $this->assertSame([], $this->propRefl->getValue($bag));
        $this->assertSame(['username' => $property], $this->propRefl->getValue($newBag));
    }

    /**
     * @testdox Can merge two bags. When properties overlaps, the existing ones are kept.
     */
    public function testMergeTwoBags()
    {
        $propertyA1 = new Property('username', 'alice');
        $propertyA2 = new Property('owner', 'bob');

        $propertyB1 = new Property('username', 'mad');  // overlapping value
        $propertyB2 = new Property('mail', 'bob@ex.com');

        $bagA = (new PropertyBag())
            ->with($propertyA1)
            ->with($propertyA2)
        ;
        $bagB = (new PropertyBag())
            ->with($propertyB1)
            ->with($propertyB2)
        ;

        $bag = $bagA->mergeWith($bagB);

        $this->assertInstanceOf(PropertyBag::class, $bag);
        $this->assertSame(
            [
                'username' => $propertyA1,
                'owner' => $propertyA2,
            ],
            $this->propRefl->getValue($bagA)
        );
        $this->assertSame(
            [
                'username' => $propertyB1,
                'mail' => $propertyB2,
            ],
            $this->propRefl->getValue($bagB)
        );
        $this->assertSame(
            [
                'username' => $propertyA1,
                'mail' => $propertyB2,
                'owner' => $propertyA2,
            ],
            $this->propRefl->getValue($bag)
        );
    }

    public function testIsIterable()
    {
        $property1 = new Property('username', 'alice');
        $property2 = new Property('owner', 'bob');

        $bag = (new PropertyBag())
            ->with($property1)
            ->with($property2)
        ;

        $array = [];
        foreach ($bag as $index => $property) {
            $array[$index] = $property;
        }

        $this->assertSame(
            [
                $property1,
                $property2,
            ],
            $array
        );
    }

    public function testIsCountable()
    {
        $bag = new PropertyBag();
        $this->assertCount(0, $bag);

        $bag = $bag->with(new Property('foo', 'bar'));
        $this->assertCount(1, $bag);

        $bag = $bag->with(new Property('foo', 'baz'));
        $this->assertCount(1, $bag);

        $bag = $bag->with(new Property('ping', 'pong'));
        $this->assertCount(2, $bag);
    }

    public function testIsEmpty()
    {
        $bag = new PropertyBag();
        $this->assertTrue($bag->isEmpty());

        $bag = $bag->with(new Property('foo', null));
        $this->assertFalse($bag->isEmpty());
    }
}
