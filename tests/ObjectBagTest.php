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

namespace Nelmio\Alice;

use Nelmio\Alice\Definition\Object\CompleteObject;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Entity\StdClassFactory;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\ObjectBag
 */
class ObjectBagTest extends TestCase
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
        $this->propRefl = (new \ReflectionClass(ObjectBag::class))->getProperty('objects');
        $this->propRefl->setAccessible(true);
    }

    public function testBagsCanBeInstantiatedWithRegularObjects()
    {
        $objects = [
            'user1' => $u1 = new stdClass(),
            'user2' => $u2 = new stdClass(),
        ];
        $u1->name = 'bob';
        $u2->name = 'alice';
        $bag = new ObjectBag($objects);

        $this->assertSameObjects(
            [
                'user1' => new CompleteObject(new SimpleObject('user1', $u1)),
                'user2' => new CompleteObject(new SimpleObject('user2', $u2)),
            ],
            $bag
        );
    }

    public function testBagsCanBeInstantiatedWithObjects()
    {
        $u1 = new stdClass();
        $u1->name = 'bob';
        $u2 = new stdClass();
        $u2->name = 'alice';

        $bag = new ObjectBag([
            'user1' => new CompleteObject(new SimpleObject('user1', $u1)),
            'user2' => new CompleteObject(new SimpleObject('user2', $u2)),
        ]);

        $this->assertSameObjects(
            [
                'user1' => new CompleteObject(new SimpleObject('user1', $u1)),
                'user2' => new CompleteObject(new SimpleObject('user2', $u2)),
            ],
            $bag
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Reference key mismatch, the keys "foo" and "bar" refers to the same fixture but the keys are different.
     */
    public function testThrowsAnExceptionIfAReferenceMismatchIsFound()
    {
        new ObjectBag([
            'foo' => new CompleteObject(new SimpleObject('bar', new stdClass())),
        ]);
    }

    public function testBagsCanBeInstantiatedWithoutAnyObject()
    {
        $bag = new ObjectBag();
        $this->assertSameObjects(
            [],
            $bag
        );
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $bag = new ObjectBag([
            'user1' => new stdClass(),
            'group1' => new stdClass(),
        ]);

        $user1Fixture = $this->createFixture('user1', stdClass::class);
        $group1Fixture = $this->createFixture('group1', stdClass::class);
        $inexistingReference = $this->createFixture('unknown', 'Dummy');

        $this->assertTrue($bag->has($user1Fixture));
        $this->assertEquals(
            new CompleteObject(new SimpleObject('user1', new stdClass())),
            $bag->get($user1Fixture)
        );

        $this->assertTrue($bag->has($group1Fixture));
        $this->assertEquals(
            new CompleteObject(new SimpleObject('group1', new stdClass())),
            $bag->get($group1Fixture)
        );

        $this->assertFalse($bag->has($inexistingReference));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\ObjectNotFoundException
     * @expectedExceptionMessage Could not find the object "foo" of the class "Dummy".
     */
    public function testThrowsExceptionWhenTryingToGetInexistingObject()
    {
        $bag = new ObjectBag();
        $bag->get($this->createFixture('foo', 'Dummy'));
    }

    public function testNamedConstructorReturnNewModifiedInstanceWhenAddingAnObject()
    {
        $bag = new ObjectBag(['foo' => new stdClass()]);

        $std = StdClassFactory::create([
            'ping' => 'pong',
        ]);

        $newBag = $bag->with(new CompleteObject(new SimpleObject('bar', $std)));

        $this->assertEquals(
            new ObjectBag(['foo' => new stdClass()]),
            $bag
        );
        $this->assertEquals(
            new ObjectBag([
                'foo' => new stdClass(),
                'bar' => new CompleteObject(new SimpleObject('bar', $std)),
            ]),
            $newBag
        );
    }

    public function testAddingAnObjectCanOverrideTheExistingOne()
    {
        $bag = new ObjectBag(['foo' => new stdClass()]);

        $std = StdClassFactory::create([
            'ping' => 'pong',
        ]);

        $newBag = $bag->with(new CompleteObject(new SimpleObject('foo', $std)));

        $this->assertEquals(
            new ObjectBag([
                'foo' => new CompleteObject(
                    new SimpleObject(
                        'foo',
                        StdClassFactory::create([
                            'ping' => 'pong',
                        ])
                    )
                ),
            ]),
            $newBag
        );
    }

    public function testNamedConstructorReturnNewModifiedInstanceWhenRemovingAnObject()
    {
        $bag = new ObjectBag(['foo' => new stdClass()]);

        $newBag = $bag->without(
            new SimpleObject('foo', new stdClass())
        );

        $this->assertEquals(
            new ObjectBag(['foo' => new stdClass()]),
            $bag
        );
        $this->assertEquals(
            new ObjectBag([]),
            $newBag
        );
    }

    public function testCanRemoveAnNonExistentObject()
    {
        $bag = new ObjectBag([]);

        $newBag = $bag->without(
            new SimpleObject('foo', new stdClass())
        );

        $this->assertEquals(
            new ObjectBag([]),
            $newBag
        );
    }

    public function testImmutableMerge()
    {
        $std1 = new stdClass();
        $std1->id = 1;

        $std2 = new stdClass();
        $std2->id = 2;

        $std3 = new stdClass();
        $std3->id = 3;

        $std4 = new Dummy();

        $object1 = new CompleteObject(new SimpleObject('foo', $std1));
        $object2 = new CompleteObject(new SimpleObject('bar', $std2));
        $object3 = new CompleteObject(new SimpleObject('bar', $std3));
        $object4 = new CompleteObject(new SimpleObject('baz', $std4));

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
        $object1 = new CompleteObject(new SimpleObject('foo', new stdClass()));
        $object2 = new CompleteObject(new SimpleObject('bar', new stdClass()));
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
        $object1 = new CompleteObject(new SimpleObject('foo', new stdClass()));
        $object2 = new CompleteObject(new SimpleObject('bar', new stdClass()));
        $bag = (new ObjectBag())->with($object1)->with($object2);

        $this->assertEquals(
            [
                'foo' => new stdClass(),
                'bar' => new stdClass(),
            ],
            $bag->toArray()
        );
        $this->assertCount(count($bag), $bag->toArray());
    }

    public function testCountable()
    {
        $this->assertTrue(is_a(ObjectBag::class, \Countable::class, true));

        $bag = new ObjectBag();
        $this->assertEquals(0, $bag->count());

        $bag = new ObjectBag([
            'foo' => new stdClass(),
            'bar' => new stdClass(),
        ]);
        $this->assertEquals(2, $bag->count());

        $object1 = new CompleteObject(new SimpleObject('foo', new stdClass()));
        $object2 = new CompleteObject(new SimpleObject('bar', new stdClass()));
        $bag = (new ObjectBag())->with($object1)->with($object2);
        $this->assertEquals(2, $bag->count());

        $object3 = new CompleteObject(new SimpleObject('foz', new stdClass()));
        $object4 = new CompleteObject(new SimpleObject('baz', new stdClass()));
        $anotherBag = (new ObjectBag())->with($object3)->with($object4);
        $bag = $bag->mergeWith($anotherBag);
        $this->assertEquals(4, $bag->count());
    }

    private function assertSameObjects(array $expected, ObjectBag $actual)
    {
        $actualObjects = $this->propRefl->getValue($actual);

        $this->assertEquals($expected, $actualObjects);
        $this->assertCount(count($expected), $actualObjects);
    }

    private function createFixture(string $reference, string $className): FixtureInterface
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn($reference);
        $fixtureProphecy->getClassName()->willReturn($className);

        return $fixtureProphecy->reveal();
    }
}
