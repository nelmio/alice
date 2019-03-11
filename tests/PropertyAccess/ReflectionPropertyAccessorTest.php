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
/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\PropertyAccess;

use Nelmio\Alice\Entity\DummyWithPrivateProperty;
use Nelmio\Alice\Entity\DummyWithPrivatePropertyChild;
use Nelmio\Alice\Entity\DummyWithPublicProperty;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @covers \Nelmio\Alice\PropertyAccess\ReflectionPropertyAccessor
 */
class ReflectionPropertyAccessorTest extends TestCase
{
    public function testIsAPropertyAccessor()
    {
        $this->assertTrue(is_a(ReflectionPropertyAccessor::class, PropertyAccessorInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ReflectionPropertyAccessor::class))->isCloneable());
    }

    public function testSetValueOnNoSuchPropertyException()
    {
        $object = new DummyWithPrivateProperty();
        $property = 'val';
        $value = 'bar';

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->setValue($object, $property, $value)
            ->willThrow(NoSuchPropertyException::class)
        ;
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);
        $accessor->setValue($object, $property, $value);

        $this->assertSame($value, $object->test_get_val());
    }

    public function testSetParentValueOnNoSuchPropertyException()
    {
        $object = new DummyWithPrivatePropertyChild();
        $property = 'val';
        $value = 'bar';

        $expected = new DummyWithPrivatePropertyChild('bar');

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->setValue($object, $property, $value)
            ->willThrow(NoSuchPropertyException::class)
        ;
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);
        $accessor->setValue($object, $property, $value);

        $this->assertSame($value, $object->test_get_val());
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     * @expectedExceptionMessage Cannot set property "unknown".
     */
    public function testThrowsAnOriginalExceptionIfSetValueForANonExistentProperty()
    {
        $property = 'unknown';
        $object = new DummyWithPrivateProperty();
        $value = 'bar';

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->setValue($object, $property, $value)
            ->willThrow(new NoSuchPropertyException("Cannot set property \"$property\"."))
        ;

        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);

        $accessor->setValue($object, $property, $value);
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     * @expectedExceptionMessage Cannot set property "unknown".
     */
    public function testThrowsAnOriginalExceptionIfSetValueForANonExistentPropertyOnNonObject()
    {
        $property = 'unknown';
        $object = [];
        $value = 'bar';

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->setValue($object, $property, $value)
            ->willThrow(new NoSuchPropertyException("Cannot set property \"$property\"."))
        ;

        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);

        $accessor->setValue($object, $property, $value);
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     * @expectedExceptionMessage Cannot set property "staticVal".
     */
    public function testThrowsAnOriginalExceptionIfSetValueForANonExistentPropertyIsStatic()
    {
        $property = 'staticVal';
        $object = new DummyWithPrivateProperty();
        $value = 'bar';

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->setValue($object, $property, $value)
            ->willThrow(new NoSuchPropertyException("Cannot set property \"$property\"."))
        ;

        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);

        $accessor->setValue($object, $property, $value);
    }

    public function testSetValueWithTheDecoratedAccessorWhenPossible()
    {
        $object = new DummyWithPublicProperty();
        $property = 'val';
        $value = 'bar';

        $expected = new DummyWithPublicProperty();
        $expected->val = 'bar';

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->setValue($object, $property, $value)
            ->will(
                function ($args) {
                    $args[0]->{$args[1]} = $args[2];
                }
            )
        ;
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);
        $accessor->setValue($object, $property, $value);

        $this->assertEquals($expected, $object);

        $decoratedAccessorProphecy->setValue(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testGetPrivateValueOnNoSuchPropertyException()
    {
        $property = 'val';
        $value = 'foo';
        $object = new DummyWithPrivateProperty($value);

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->getValue($object, $property)
            ->willThrow(NoSuchPropertyException::class)
        ;

        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);
        $actual = $accessor->getValue($object, $property);

        $this->assertEquals($value, $actual);
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     * @expectedExceptionMessage Cannot read property "foo".
     */
    public function testThrowsAnOriginalExceptionIfPropertyDoesNotExist()
    {
        $property = 'foo';
        $object = new DummyWithPrivateProperty();

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->getValue($object, $property)
            ->willThrow(new NoSuchPropertyException("Cannot read property \"$property\"."))
        ;

        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);

        $accessor->getValue($object, $property);
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     * @expectedExceptionMessage Cannot read property "foo".
     */
    public function testThrowsAnOriginalExceptionIfPropertyDoesNotExistOnNonObject()
    {
        $property = 'foo';
        $object = [];

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->getValue($object, $property)
            ->willThrow(new NoSuchPropertyException("Cannot read property \"$property\"."))
        ;

        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);

        $accessor->getValue($object, $property);
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     * @expectedExceptionMessage Cannot read property "staticVal".
     */
    public function testThrowsAnOriginalExceptionIfPropertyIsStatic()
    {
        $property = 'staticVal';
        $object = new DummyWithPrivateProperty();

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->getValue($object, $property)
            ->willThrow(new NoSuchPropertyException("Cannot read property \"$property\"."))
        ;

        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);

        $accessor->getValue($object, $property);
    }

    public function testGetValueWithTheDecoratedAccessorWhenPossible()
    {
        $property = 'val';
        $value = $expected = 'bar';
        $object = new DummyWithPublicProperty($value);

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->getValue($object, $property)
            ->willReturn($value)
        ;
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);
        $actual = $accessor->getValue($object, $property);

        $this->assertEquals($expected, $actual);

        $decoratedAccessorProphecy->getValue(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testExistingClassPropertiesAreAlwaysWritable()
    {
        $object = new DummyWithPrivateProperty();
        $objectWithInheritance = new DummyWithPrivatePropertyChild();

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->isWritable($object, Argument::any())
            ->willReturn(false)
        ;
        $decoratedAccessorProphecy
            ->isWritable($objectWithInheritance, Argument::any())
            ->willReturn(false)
        ;
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);

        $this->assertTrue($accessor->isWritable($object, 'val'), 'writable if the property exists');
        $this->assertFalse($accessor->isWritable($object, 'foo'), 'non writable if the property does not exist');
        $this->assertFalse($accessor->isWritable($object, 'staticVal'), 'non writable if the property is static');

        $this->assertTrue($accessor->isWritable($objectWithInheritance, 'val'), 'writable if the property exists');
        $this->assertTrue($accessor->isWritable($objectWithInheritance, 'val2'), 'writable if the property exists');
        $this->assertFalse($accessor->isWritable($objectWithInheritance, 'foo'), 'non writable if the property does not exist');
        $this->assertFalse($accessor->isWritable($objectWithInheritance, 'staticVal'), 'non writable if the property is static');
    }

    public function testUsesDecoratedAccessorToDetermineIfPropertyIsWritable()
    {
        $object = new DummyWithPublicProperty();
        $property = 'val';

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->isWritable($object, $property)
            ->willReturn($expected = true)
        ;
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);
        $actual = $accessor->isWritable($object, $property);

        $this->assertEquals($expected, $actual);

        $decoratedAccessorProphecy->isWritable(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testPrivateClassPropertiesAreReadableOnlyIfTheyExists()
    {
        $object = new DummyWithPrivateProperty();
        $objectWithInheritance = new DummyWithPrivatePropertyChild();

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->isReadable($object, Argument::any())
            ->willReturn(false)
        ;
        $decoratedAccessorProphecy
            ->isReadable($objectWithInheritance, Argument::any())
            ->willReturn(false)
        ;
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);

        $this->assertTrue($accessor->isReadable($object, 'val'), 'readable if the property exists');
        $this->assertFalse($accessor->isReadable($object, 'foo'), 'non readable if the property does not exist');
        $this->assertFalse($accessor->isReadable($object, 'staticVal'), 'non readable if the property is static');

        $this->assertTrue($accessor->isReadable($objectWithInheritance, 'val'), 'readable if the property exists');
        $this->assertTrue($accessor->isReadable($objectWithInheritance, 'val2'), 'readable if the property exists');
        $this->assertFalse($accessor->isReadable($objectWithInheritance, 'foo'), 'non readable if the property does not exist');
        $this->assertFalse($accessor->isReadable($objectWithInheritance, 'staticVal'), 'non readable if the property is static');
    }

    public function testUsesDecoratedAccessorToDetermineIfPropertyIsReadable()
    {
        $object = new DummyWithPublicProperty();
        $property = 'val';

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->isReadable($object, $property)
            ->willReturn($expected = true)
        ;
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new ReflectionPropertyAccessor($decoratedAccessor);
        $actual = $accessor->isReadable($object, $property);

        $this->assertEquals($expected, $actual);

        $decoratedAccessorProphecy->isReadable(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
