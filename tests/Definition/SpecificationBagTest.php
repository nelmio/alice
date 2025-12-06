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

use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(SpecificationBag::class)]
final class SpecificationBagTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $constructor = new FakeMethodCall();
        $properties = new PropertyBag();
        $calls = new MethodCallBag();

        $bag = new SpecificationBag($constructor, $properties, $calls);
        self::assertEquals($constructor, $bag->getConstructor());
        self::assertEquals($properties, $bag->getProperties());
        self::assertEquals($calls, $bag->getMethodCalls());

        $constructor = null;

        $bag = new SpecificationBag($constructor, $properties, $calls);
        self::assertEquals($constructor, $bag->getConstructor());
        self::assertEquals($properties, $bag->getProperties());
        self::assertEquals($calls, $bag->getMethodCalls());
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $constructor = null;
        $properties = new PropertyBag();
        $calls = new MethodCallBag();
        $bag = new SpecificationBag($constructor, $properties, $calls);

        $newConstructor = new FakeMethodCall();
        $newBag = $bag->withConstructor($newConstructor);

        self::assertInstanceOf(SpecificationBag::class, $newBag);

        self::assertEquals($constructor, $bag->getConstructor());
        self::assertEquals($calls, $bag->getMethodCalls());
        self::assertEquals($properties, $bag->getProperties());

        self::assertEquals(new FakeMethodCall(), $newBag->getConstructor());
        self::assertEquals($calls, $newBag->getMethodCalls());
        self::assertEquals($properties, $newBag->getProperties());
    }

    public function testMergeTwoBags(): void
    {
        $constructorA = null;
        $constructorB = null;

        $propertyA1 = new Property('username', 'alice');
        $propertyA2 = new Property('owner', 'bob');

        $propertyB1 = new Property('username', 'mad');
        $propertyB2 = new Property('mail', 'bob@ex.com');

        $propertiesA = (new PropertyBag())
            ->with($propertyA1)
            ->with($propertyA2);
        $propertiesB = (new PropertyBag())
            ->with($propertyB1)
            ->with($propertyB2);

        $callA1 = new SimpleMethodCall('setUsername', []);
        $callA2 = new SimpleMethodCall('setOwner', []);

        $callB1 = new SimpleMethodCall('setUsername', []);
        $callB2 = new SimpleMethodCall('setMail', []);

        $callsA = (new MethodCallBag())
            ->with($callA1)
            ->with($callA2);
        $callsB = (new MethodCallBag())
            ->with($callB1)
            ->with($callB2);

        $bagA = new SpecificationBag($constructorA, $propertiesA, $callsA);
        $bagB = new SpecificationBag($constructorB, $propertiesB, $callsB);
        $bag = $bagA->mergeWith($bagB);

        self::assertInstanceOf(SpecificationBag::class, $bag);
        self::assertEquals($constructorA, $bagA->getConstructor());
        self::assertEquals($propertiesA, $bagA->getProperties());
        self::assertEquals($callsA, $bagA->getMethodCalls());

        self::assertEquals($constructorB, $bagB->getConstructor());
        self::assertEquals($propertiesB, $bagB->getProperties());
        self::assertEquals($callsB, $bagB->getMethodCalls());

        self::assertEquals($constructorA, $bag->getConstructor());
    }

    #[\PHPUnit\Framework\Attributes\TestDox('Merging a bag that has a constructor method with a new one that does not, the result will have a constructor method.')]
    public function testMergeTwoBags1(): void
    {
        $constructorA = new SimpleMethodCall('create', []);
        $constructorB = null;

        $bagA = new SpecificationBag($constructorA, new PropertyBag(), new MethodCallBag());
        $bagB = new SpecificationBag($constructorB, new PropertyBag(), new MethodCallBag());
        $bag = $bagA->mergeWith($bagB);

        self::assertEquals($constructorA, $bagA->getConstructor());
        self::assertEquals($constructorB, $bagB->getConstructor());
        self::assertEquals($constructorA, $bag->getConstructor());
    }

    #[\PHPUnit\Framework\Attributes\TestDox('Merging a bag that has a constructor method with a new one that has one as well, the result will kept its constructor method.')]
    public function testMergeTwoBags2(): void
    {
        $constructorA = new SimpleMethodCall('childCreate', []);
        $constructorB = new SimpleMethodCall('parentCreate', []);

        $bagA = new SpecificationBag($constructorA, new PropertyBag(), new MethodCallBag());
        $bagB = new SpecificationBag($constructorB, new PropertyBag(), new MethodCallBag());
        $bag = $bagA->mergeWith($bagB);

        self::assertEquals($constructorA, $bagA->getConstructor());
        self::assertEquals($constructorB, $bagB->getConstructor());
        self::assertEquals($constructorA, $bag->getConstructor());
    }
}
