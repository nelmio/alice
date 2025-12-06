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

use Countable;
use InvalidArgumentException;
use Nelmio\Alice\Definition\Object\CompleteObject;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Entity\StdClassFactory;
use Nelmio\Alice\Throwable\Exception\ObjectNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

/**
 * @internal
 */
#[CoversClass(ObjectBag::class)]
final class ObjectBagTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ReflectionProperty
     */
    private $propRefl;

    protected function setUp(): void
    {
        $this->propRefl = (new ReflectionClass(ObjectBag::class))->getProperty('objects');
    }

    public function testBagsCanBeInstantiatedWithRegularObjects(): void
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
            $bag,
        );
    }

    public function testBagsCanBeInstantiatedWithObjects(): void
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
            $bag,
        );
    }

    public function testThrowsAnExceptionIfAReferenceMismatchIsFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reference key mismatch, the keys "foo" and "bar" refers to the same fixture but the keys are different.');

        new ObjectBag([
            'foo' => new CompleteObject(new SimpleObject('bar', new stdClass())),
        ]);
    }

    public function testBagsCanBeInstantiatedWithoutAnyObject(): void
    {
        $bag = new ObjectBag();
        $this->assertSameObjects(
            [],
            $bag,
        );
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $bag = new ObjectBag([
            'user1' => new stdClass(),
            'group1' => new stdClass(),
        ]);

        $user1Fixture = $this->createFixture('user1', stdClass::class);
        $group1Fixture = $this->createFixture('group1', stdClass::class);
        $inexistingReference = $this->createFixture('unknown', 'Dummy');

        self::assertTrue($bag->has($user1Fixture));
        self::assertEquals(
            new CompleteObject(new SimpleObject('user1', new stdClass())),
            $bag->get($user1Fixture),
        );

        self::assertTrue($bag->has($group1Fixture));
        self::assertEquals(
            new CompleteObject(new SimpleObject('group1', new stdClass())),
            $bag->get($group1Fixture),
        );

        self::assertFalse($bag->has($inexistingReference));
    }

    public function testThrowsExceptionWhenTryingToGetInexistingObject(): void
    {
        $this->expectException(ObjectNotFoundException::class);
        $this->expectExceptionMessage('Could not find the object "foo" of the class "Dummy".');

        $bag = new ObjectBag();
        $bag->get($this->createFixture('foo', 'Dummy'));
    }

    public function testNamedConstructorReturnNewModifiedInstanceWhenAddingAnObject(): void
    {
        $bag = new ObjectBag(['foo' => new stdClass()]);

        $std = StdClassFactory::create([
            'ping' => 'pong',
        ]);

        $newBag = $bag->with(new CompleteObject(new SimpleObject('bar', $std)));

        self::assertEquals(
            new ObjectBag(['foo' => new stdClass()]),
            $bag,
        );
        self::assertEquals(
            new ObjectBag([
                'foo' => new stdClass(),
                'bar' => new CompleteObject(new SimpleObject('bar', $std)),
            ]),
            $newBag,
        );
    }

    public function testAddingAnObjectCanOverrideTheExistingOne(): void
    {
        $bag = new ObjectBag(['foo' => new stdClass()]);

        $std = StdClassFactory::create([
            'ping' => 'pong',
        ]);

        $newBag = $bag->with(new CompleteObject(new SimpleObject('foo', $std)));

        self::assertEquals(
            new ObjectBag([
                'foo' => new CompleteObject(
                    new SimpleObject(
                        'foo',
                        StdClassFactory::create([
                            'ping' => 'pong',
                        ]),
                    ),
                ),
            ]),
            $newBag,
        );
    }

    public function testNamedConstructorReturnNewModifiedInstanceWhenRemovingAnObject(): void
    {
        $bag = new ObjectBag(['foo' => new stdClass()]);

        $newBag = $bag->without(
            new SimpleObject('foo', new stdClass()),
        );

        self::assertEquals(
            new ObjectBag(['foo' => new stdClass()]),
            $bag,
        );
        self::assertEquals(
            new ObjectBag([]),
            $newBag,
        );
    }

    public function testCanRemoveAnNonExistentObject(): void
    {
        $bag = new ObjectBag([]);

        $newBag = $bag->without(
            new SimpleObject('foo', new stdClass()),
        );

        self::assertEquals(
            new ObjectBag([]),
            $newBag,
        );
    }

    public function testImmutableMerge(): void
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

        self::assertInstanceOf(ObjectBag::class, $bag);
        $this->assertSameObjects(
            [
                'foo' => $object1,
                'bar' => $object2,
            ],
            $bag1,
        );
        $this->assertSameObjects(
            [
                'bar' => $object3,
                'baz' => $object4,
            ],
            $bag2,
        );
        $this->assertSameObjects(
            [
                'foo' => $object1,
                'bar' => $object3,
                'baz' => $object4,
            ],
            $bag,
        );
    }

    public function testIsTraversable(): void
    {
        $object1 = new CompleteObject(new SimpleObject('foo', new stdClass()));
        $object2 = new CompleteObject(new SimpleObject('bar', new stdClass()));
        $bag = (new ObjectBag())->with($object1)->with($object2);

        $traversed = [];
        foreach ($bag as $reference => $object) {
            $traversed[$reference] = $object;
        }

        self::assertSame(
            [
                'foo' => $object1,
                'bar' => $object2,
            ],
            $traversed,
        );
    }

    public function testToArray(): void
    {
        $object1 = new CompleteObject(new SimpleObject('foo', new stdClass()));
        $object2 = new CompleteObject(new SimpleObject('bar', new stdClass()));
        $bag = (new ObjectBag())->with($object1)->with($object2);

        self::assertEquals(
            [
                'foo' => new stdClass(),
                'bar' => new stdClass(),
            ],
            $bag->toArray(),
        );
        self::assertCount(count($bag), $bag->toArray());
    }

    public function testCountable(): void
    {
        self::assertTrue(is_a(ObjectBag::class, Countable::class, true));

        $bag = new ObjectBag();
        self::assertCount(0, $bag);

        $bag = new ObjectBag([
            'foo' => new stdClass(),
            'bar' => new stdClass(),
        ]);
        self::assertCount(2, $bag);

        $object1 = new CompleteObject(new SimpleObject('foo', new stdClass()));
        $object2 = new CompleteObject(new SimpleObject('bar', new stdClass()));
        $bag = (new ObjectBag())->with($object1)->with($object2);
        self::assertCount(2, $bag);

        $object3 = new CompleteObject(new SimpleObject('foz', new stdClass()));
        $object4 = new CompleteObject(new SimpleObject('baz', new stdClass()));
        $anotherBag = (new ObjectBag())->with($object3)->with($object4);
        $bag = $bag->mergeWith($anotherBag);
        self::assertCount(4, $bag);
    }

    private function assertSameObjects(array $expected, ObjectBag $actual): void
    {
        $actualObjects = $this->propRefl->getValue($actual);

        self::assertEquals($expected, $actualObjects);
        self::assertCount(count($expected), $actualObjects);
    }

    private function createFixture(string $reference, string $className): FixtureInterface
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn($reference);
        $fixtureProphecy->getClassName()->willReturn($className);

        return $fixtureProphecy->reveal();
    }
}
