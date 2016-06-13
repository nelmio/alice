<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

use Nelmio\Alice\Definition\Object\SimpleObject;

/**
 * @covers Nelmio\Alice\ObjectBag
 */
class ObjectBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $propRefl;

    public function setUp()
    {
        $this->propRefl = (new \ReflectionClass(ObjectBag::class))->getProperty('objects');
        $this->propRefl->setAccessible(true);
    }

    public function testConstructBag()
    {
        $objects = [
            'foo' => new \stdClass(),
            'bar' => new \stdClass(),
        ];
        $bag = new ObjectBag($objects);

        $this->assertSameObjects(
            [
                'foo' => new SimpleObject('foo', new \stdClass()),
                'bar' => new SimpleObject('bar', new \stdClass()),
            ],
            $bag
        );

        $objects = [];
        $bag = new ObjectBag($objects);

        $this->assertSameObjects(
            [],
            $bag
        );

        $objects = [
            0 => new \stdClass(),
        ];
        $bag = new ObjectBag($objects);

        $this->assertSameObjects(
            [
                '0' => new SimpleObject('0', new \stdClass()),
            ],
            $bag
        );
    }

    public function testImmutableMutator()
    {
        $object1 = new SimpleObject('foo', new \stdClass());
        $object2 = new SimpleObject('bar', new \stdClass());

        $bag = new ObjectBag();
        $bag1 = $bag->with($object1);
        $bag2 = $bag1->with($object2);

        $this->assertInstanceOf(ObjectBag::class, $bag1);
        $this->assertSameObjects([], $bag);
        $this->assertSameObjects(
            [
                'foo' => $object1,
            ],
            $bag1
        );
        $this->assertSameObjects(
            [
                'foo' => $object1,
                'bar' => $object2,
            ],
            $bag2
        );
    }

    public function testImmutableMerge()
    {
        $std1 = new \stdClass();
        $std1->id = 1;

        $std2 = new \stdClass();
        $std2->id = 2;

        $std3 = new \stdClass();
        $std3->id = 3;

        $std4 = new \stdClass();
        $std4->id = 4;

        $object1 = new SimpleObject('foo', $std1);
        $object2 = new SimpleObject('bar', $std2);
        $object3 = new SimpleObject('bar', $std3);
        $object4 = new SimpleObject('baz', $std4);

        $bag1 = (new ObjectBag())->with($object1)->with($object2);
        $bag2 = (new ObjectBag())->with($object3)->with($object4);
        $bag = $bag1->mergeWith($bag2);

        $this->assertInstanceOf(ObjectBag::class, $bag);
        $this->assertSameObjects(
            [
                'foo' => $object1,
                'bar' => $object2,
            ],
            $bag1
        );
        $this->assertSameObjects(
            [
                'bar' => $object3,
                'baz' => $object4,
            ],
            $bag2
        );
        $this->assertSameObjects(
            [
                'foo' => $object1,
                'bar' => $object3,
                'baz' => $object4,
            ],
            $bag
        );
    }

    public function testIsTraversable()
    {
        $object1 = new SimpleObject('foo', new \stdClass());
        $object2 = new SimpleObject('bar', new \stdClass());
        $bag = (new ObjectBag())->with($object1)->with($object2);

        $traversed = [];
        foreach ($bag as $reference => $object) {
            $traversed[$reference] = $object;
        }

        $this->assertSame(
            [
                'foo' => $object1,
                'bar' => $object2,
            ],
            $traversed
        );
    }

    private function assertSameObjects(array $expected, ObjectBag $actual)
    {
        $actualObjects = $this->propRefl->getValue($actual);

        $this->assertEquals($expected, $actualObjects);
        $this->assertEquals(count($expected), count($actualObjects));
    }
}
