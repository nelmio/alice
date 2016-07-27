<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Hydrator;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\HydratorInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @covers Nelmio\Alice\Generator\Hydrator\PropertyAccessorHydrator
 */
class PropertyAccessorHydratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyAccessorHydrator
     */
    private $hydrator;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function setUp()
    {
        $this->propertyAccessor = new PropertyAccessor();
        $this->hydrator = new PropertyAccessorHydrator($this->propertyAccessor);
    }

    public function testIsAnHydrator()
    {
        $this->assertTrue(is_a(PropertyAccessorHydrator::class, HydratorInterface::class, true));
    }

    public function testItReturnsModifiedObject()
    {
        $property = new Property('username', 'bob');
        $instance = new \stdClass();
        $object = new SimpleObject('dummy', $instance);

        $accessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        /** @var PropertyAccessorInterface $accessor */
        $accessor = $accessorProphecy->reveal();

        $hydrator = new PropertyAccessorHydrator($accessor);
        $result = $hydrator->hydrate($object, $property);

        $this->assertEquals($object, $result);
    }

    /**
     * @dataProvider provideProperties
     */
    public function testSetValue(Property $property)
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
