<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition;

/**
 * @covers Nelmio\Alice\Definition\PropertyBag
 */
class PropertyBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $propRefl;

    public function setUp()
    {
        $refl = new \ReflectionClass(PropertyBag::class);
        $propRefl = $refl->getProperty('properties');
        $propRefl->setAccessible(true);

        $this->propRefl = $propRefl;
    }

    public function testMutatorsAreImmutable()
    {
        $property = new Property('username', 'alice');

        $bag = new PropertyBag();
        $newBag = $bag->with($property);

        $this->assertInstanceOf(PropertyBag::class, $newBag);
        $this->assertNotSame($newBag, $bag);
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
}
