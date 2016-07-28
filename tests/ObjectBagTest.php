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
use Nelmio\Alice\Exception\ObjectNotFoundException;

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
            'user1' => new \stdClass(),
            'user2' => new \stdClass(),
        ];
        $bag = new ObjectBag($objects);

        $this->assertSameObjects(
            [
                'user1' => new SimpleObject('user1', new \stdClass()),
                'user2' => new SimpleObject('user2', new \stdClass()),
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

    public function testReadAccessorsReturnPropertiesValues()
    {
        $objects = [
            'user1' => new \stdClass(),
            'group1' => new \stdClass(),
        ];
        $bag = new ObjectBag($objects);

        $user1Fixture = $this->createFixture('user1', \stdClass::class);
        $group1Fixture = $this->createFixture('group1', \stdClass::class);
        $inexistingReference = $this->createFixture('unknown', 'Dummy');

        $this->assertTrue($bag->has($user1Fixture));
        $this->assertEquals(
            new SimpleObject('user1', new \stdClass()),
            $bag->get($user1Fixture)
        );

        $this->assertTrue($bag->has($group1Fixture));
        $this->assertEquals(
            new SimpleObject('group1', new \stdClass()),
            $bag->get($group1Fixture)
        );

        $this->assertFalse($bag->has($inexistingReference));
        try {
            $bag->get($inexistingReference);
            $this->fail('Expected exception to be thrown.');
        } catch (ObjectNotFoundException $exception) {
            $this->assertEquals(
                'Could not find the object "unknown" of the class "Dummy".',
                $exception->getMessage()
            );
        }
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $object1 = new SimpleObject('foo', new \stdClass());
        $object2 = new SimpleObject('bar', new \stdClass());
        $object3 = new SimpleObject('foo', new Dummy());
        $object4 = new SimpleObject('baz', new AnotherDummy());

        $bag = new ObjectBag();
        $bag1 = $bag
            ->with($object1)
            ->with($object2)
        ;
        $bag2 = $bag1
            ->with($object3)
            ->with($object4)
        ;

        $this->assertInstanceOf(ObjectBag::class, $bag1);
        $this->assertSameObjects([], $bag);
        $this->assertSameObjects(
            [
                'foo' => $object1,
                'bar' => $object2,
            ],
            $bag1
        );
        $this->assertSameObjects(
            [
                'foo' => $object3,
                'bar' => $object2,
                'baz' => $object4,
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

        $std4 = new Dummy();

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

    public function testToArray()
    {
        $object1 = new SimpleObject('foo', new \stdClass());
        $object2 = new SimpleObject('bar', new \stdClass());
        $bag = (new ObjectBag())->with($object1)->with($object2);

        $this->assertEquals(
            [
                'foo' => $object1->getInstance(),
                'bar' => $object2->getInstance(),
            ],
            $bag->toArray()
        );
        $this->assertEquals(count($bag), count($bag->toArray()));
    }

    public function testCountable()
    {
        $this->assertTrue(is_a(ObjectBag::class, \Countable::class, true));

        $bag = new ObjectBag();
        $this->assertEquals(0, $bag->count());

        $bag = new ObjectBag([
            'foo' => new \stdClass(),
            'bar' => new \stdClass(),
        ]);
        $this->assertEquals(2, $bag->count());

        $object1 = new SimpleObject('foo', new \stdClass());
        $object2 = new SimpleObject('bar', new \stdClass());
        $bag = (new ObjectBag())->with($object1)->with($object2);
        $this->assertEquals(2, $bag->count());

        $object3 = new SimpleObject('foz', new \stdClass());
        $object4 = new SimpleObject('baz', new \stdClass());
        $anotherBag = (new ObjectBag())->with($object3)->with($object4);
        $bag = $bag->mergeWith($anotherBag);
        $this->assertEquals(4, $bag->count());
    }

    private function assertSameObjects(array $expected, ObjectBag $actual)
    {
        $actualObjects = $this->propRefl->getValue($actual);

        $this->assertEquals($expected, $actualObjects);
        $this->assertEquals(count($expected), count($actualObjects));
    }

    private function createFixture(string $reference, string $className): FixtureInterface
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn($reference);
        $fixtureProphecy->getClassName()->willReturn($className);

        return $fixtureProphecy->reveal();
    }
}

class Dummy
{
}

class AnotherDummy
{
}
