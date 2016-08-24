<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Hydrator\Property;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Entity\DummyWithDate;
use Nelmio\Alice\Entity\Hydrator\Dummy;
use Nelmio\Alice\Exception\Symfony\PropertyAccess\RootException as GenericPropertyAccessException;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Prophecy\Argument;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @covers Nelmio\Alice\Generator\Hydrator\Property\SymfonyPropertyAccessorHydrator
 */
class SymfonyPropertyAccessorHydratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SymfonyPropertyAccessorHydrator
     */
    private $hydrator;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function setUp()
    {
        $this->propertyAccessor = new PropertyAccessor();
        $this->hydrator = new SymfonyPropertyAccessorHydrator($this->propertyAccessor);
    }

    public function testIsAnHydrator()
    {
        $this->assertTrue(is_a(SymfonyPropertyAccessorHydrator::class, PropertyHydratorInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone $this->hydrator;
    }

    public function testReturnsHydratedObject()
    {
        $property = new Property('username', 'bob');
        $instance = new Dummy();
        $object = new SimpleObject('dummy', $instance);

        $accessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $accessorProphecy->setValue($instance, 'username', 'bob')->willReturn(null);
        /** @var PropertyAccessorInterface $accessor */
        $accessor = $accessorProphecy->reveal();

        $hydrator = new SymfonyPropertyAccessorHydrator($accessor);
        $result = $hydrator->hydrate($object, $property);

        $this->assertEquals($object, $result);

        $accessorProphecy->setValue(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Hydrator\HydrationException
     * @expectedExceptionMessage Could not access to the property "dummy" of the object "username" (class: Nelmio\Alice\Entity\Hydrator\Dummy).
     */
    public function testThrowsAnHydrationExceptionIfAnAccessExceptionIsThrown()
    {
        $property = new Property('username', 'bob');
        $instance = new Dummy();
        $object = new SimpleObject('dummy', $instance);

        $accessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $accessorProphecy->setValue(Argument::cetera())->willThrow(AccessException::class);
        /** @var PropertyAccessorInterface $accessor */
        $accessor = $accessorProphecy->reveal();

        $hydrator = new SymfonyPropertyAccessorHydrator($accessor);
        $result = $hydrator->hydrate($object, $property);

        $this->assertEquals($object, $result);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Hydrator\NoSuchPropertyException
     * @expectedExceptionMessage Could not hydrate the property "dummy" of the object "foo" (class: Nelmio\Alice\Dummy).
     */
    public function testThrowsNoPropertyExceptionIfPropertyCouldNotBeFound()
    {
        $object = new SimpleObject('dummy', new \Nelmio\Alice\Dummy());
        $property = new Property('foo', 'bar');
        $this->hydrator->hydrate($object, $property);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Hydrator\InvalidArgumentException
     * @expectedExceptionMessage Invalid value given for the property "dummy" of the object "immutableDateTime" (class: Nelmio\Alice\Entity\DummyWithDate).
     */
    public function testThrowsInvalidArgumentExceptionIfInvalidTypeIsGiven()
    {
        $object = new SimpleObject('dummy', new DummyWithDate());
        $property = new Property('immutableDateTime', 'bar');
        $this->hydrator->hydrate($object, $property);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Hydrator\HydrationException
     * @expectedExceptionMessage Could not hydrate the property "dummy" of the object "foo" (class: Nelmio\Alice\Entity\DummyWithDate).
     */
    public function testCatchesAnySymfonyPropertyAccessorToThrowAnHydratorException()
    {
        $object = new SimpleObject('dummy', new DummyWithDate());
        $property = new Property('foo', 'bar');

        $accessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $accessorProphecy
            ->setValue(Argument::cetera())
            ->willThrow(GenericPropertyAccessException::class)
        ;
        /** @var PropertyAccessorInterface $accessor */
        $accessor = $accessorProphecy->reveal();

        $hydrator = new SymfonyPropertyAccessorHydrator($accessor);
        $hydrator->hydrate($object, $property);
    }

    public function testCanHydrateStdClassObjects()
    {
        $object = new SimpleObject('dummy', new \stdClass());
        $property = new Property('foo', 'bar');

        $std = new \stdClass();
        $std->foo = 'bar';
        $expected = new SimpleObject('dummy', $std);

        $actual = $this->hydrator->hydrate($object, $property);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideProperties
     */
    public function testTestObjectHydrationAgainstMutlipleValues(Property $property)
    {
        $instance = new Dummy();
        $object = new SimpleObject('dummy', $instance);
        $hydratedObject = $this->hydrator->hydrate($object , $property);

        $expected = $property->getValue();
        $actual = $this->propertyAccessor->getValue($hydratedObject->getInstance(), $property->getName());

        $this->assertSame($expected, $actual);
    }

    public function provideProperties()
    {
        return [
            // Accessor methods
            [new Property('publicProperty', 'Bernhard')],
            [new Property('publicAccessor', 'Bernhard')],
            [new Property('publicAccessorWithDefaultValue', 'Bernhard')],
            [new Property('publicAccessorWithRequiredAndDefaultValue', 'Bernhard')],
            [new Property('publicIsAccessor', 'Bernhard')],
            [new Property('publicHasAccessor', 'Bernhard')],
            [new Property('publicGetSetter', 'Bernhard')],

            // Methods are camelized
            [new Property('public_accessor', 'Bernhard')],
            [new Property('_public_accessor', 'Bernhard')],
        ];
    }
}
