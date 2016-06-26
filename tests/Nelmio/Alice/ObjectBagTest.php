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

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\SpecificationBag;
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
            'Nelmio\Entity\User' => [
                'user1' => new \stdClass(),
                'user2' => new \stdClass(),
            ],
            'Nelmio\Entity\Group' => [
                'group1' => new \stdClass(),
                'group2' => new \stdClass(),
            ],
        ];
        $bag = new ObjectBag($objects);

        $this->assertSameObjects(
            [
                'Nelmio\Entity\User' => [
                    'user1' => new SimpleObject('user1', new \stdClass()),
                    'user2' => new SimpleObject('user2', new \stdClass()),
                ],
                'Nelmio\Entity\Group' => [
                    'group1' => new SimpleObject('group1', new \stdClass()),
                    'group2' => new SimpleObject('group2', new \stdClass()),
                ],
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
            'Nelmio\Entity\User' => [
                0 => new \stdClass(),
            ],
        ];
        $bag = new ObjectBag($objects);

        $this->assertSameObjects(
            [
                'Nelmio\Entity\User' => [
                    '0' => new SimpleObject('0', new \stdClass()),
                ],
            ],
            $bag
        );
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage Expected array of objects to be an array where keys are FQCN and values arrays of
     *                           object reference/object pairs but found a "object" instead of an array.',
     */
    public function testThrowErrorOnInvalidArgument()
    {
        new ObjectBag([
            'user1' => new \stdClass(),
        ]);
    }

    public function testAccessors()
    {
        $objects = [
            'Nelmio\Entity\User' => [
                'user1' => new \stdClass(),
            ],
            'Nelmio\Entity\Group' => [
                'group1' => new \stdClass(),
            ],
        ];
        $bag = new ObjectBag($objects);

        $user1Fixture = $this->createFixture('user1', 'Nelmio\Entity\User');
        $group1Fixture = $this->createFixture('group1', 'Nelmio\Entity\Group');
        $userWithWrongClass = $this->createFixture('user1', 'stdClass');

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

        $this->assertFalse($bag->has($userWithWrongClass));
        try {
            $bag->get($userWithWrongClass);
            $this->fail('Expected exception to be thrown.');
        } catch (ObjectNotFoundException $exception) {
            $this->assertEquals(
                'Could not find the object "user1" of the class "stdClass".',
                $exception->getMessage()
            );
        }
    }

    public function testImmutableMutator()
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
                'stdClass' => [
                    'foo' => $object1,
                    'bar' => $object2,
                ],
            ],
            $bag1
        );
        $this->assertSameObjects(
            [
                'stdClass' => [
                    'foo' => $object1,
                    'bar' => $object2,
                ],
                'Nelmio\Alice\Dummy' => [
                    'foo' => $object3,
                ],
                'Nelmio\Alice\AnotherDummy' => [
                    'baz' => $object4,
                ],
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
                'stdClass' => [
                    'foo' => $object1,
                    'bar' => $object2,
                ],
            ],
            $bag1
        );
        $this->assertSameObjects(
            [
                'stdClass' => [
                    'bar' => $object3,
                ],
                'Nelmio\Alice\Dummy' => [
                    'baz' => $object4,
                ],
            ],
            $bag2
        );
        $this->assertSameObjects(
            [
                'stdClass' => [
                    'foo' => $object1,
                    'bar' => $object3,
                ],
                'Nelmio\Alice\Dummy' => [
                    'baz' => $object4,
                ],
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
                'stdClass' => [
                    'foo' => $object1,
                    'bar' => $object2,
                ],
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

    private function createFixture(string $reference, string $className): FixtureInterface
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getReference()->willReturn($reference);
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
