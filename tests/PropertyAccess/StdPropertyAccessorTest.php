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

use Nelmio\Alice\Entity\DummyWithPublicProperty;
use Nelmio\Alice\Entity\StdClassFactory;
use Nelmio\Alice\Symfony\PropertyAccess\FakePropertyAccessor;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @covers \Nelmio\Alice\PropertyAccess\StdPropertyAccessor
 */
class StdPropertyAccessorTest extends TestCase
{
    public function testIsAPropertyAccessor()
    {
        $this->assertTrue(is_a(StdPropertyAccessor::class, PropertyAccessorInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(StdPropertyAccessor::class))->isCloneable());
    }

    public function testSetValueOfAStdClass()
    {
        $object = new \stdClass();
        $property = 'foo';
        $value = 'bar';

        $expected = StdClassFactory::create(['foo' => 'bar']);

        $accessor = new StdPropertyAccessor(new FakePropertyAccessor());
        $accessor->setValue($object, $property, $value);

        $this->assertEquals($expected, $object);
    }

    public function testSetValueWithTheDecoratedAccessorWhenTheObjectIsNotAnInstanceOfStdClass()
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

        $accessor = new StdPropertyAccessor($decoratedAccessor);
        $accessor->setValue($object, $property, $value);

        $this->assertEquals($expected, $object);

        $decoratedAccessorProphecy->setValue(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testGetValueOfAStdClass()
    {
        $object = StdClassFactory::create([$property = 'foo' => $expected = 'bar']);

        $accessor = new StdPropertyAccessor(new FakePropertyAccessor());
        $actual = $accessor->getValue($object, $property);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     * @expectedExceptionMessage Cannot read property "foo" from stdClass.
     */
    public function testThrowsAnExceptionIfPropertyNotFoundOnStdClass()
    {
        $object = new \stdClass();

        $accessor = new StdPropertyAccessor(new FakePropertyAccessor());
        $accessor->getValue($object, 'foo');
    }

    public function testGetValueWithTheDecoratedAccessorWhenTheObjectIsNotAnInstanceOfStdClass()
    {
        $object = new DummyWithPublicProperty();
        $property = 'val';
        $object->$property = $expected = 'bar';

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->getValue($object, $property)
            ->will(
                function ($args) {
                    return $args[0]->{$args[1]};
                }
            )
        ;
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new StdPropertyAccessor($decoratedAccessor);
        $actual = $accessor->getValue($object, $property);

        $this->assertEquals($expected, $actual);

        $decoratedAccessorProphecy->getValue(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testStdClassPropertiesAreAlwaysWriteable()
    {
        $object = new \stdClass();
        $accessor = new StdPropertyAccessor(new FakePropertyAccessor());

        $this->assertTrue($accessor->isWritable($object, 'foo'));
    }

    public function testUsesDecoratedAccessorToDertermineIfPropertyIsWritableIfObjectIsNotAnStdClassInstance()
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

        $accessor = new StdPropertyAccessor($decoratedAccessor);
        $actual = $accessor->isWritable($object, $property);

        $this->assertEquals($expected, $actual);

        $decoratedAccessorProphecy->isWritable(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testStdClassPropertiesAreReadableOnlyIfTheyExists()
    {
        $object = StdClassFactory::create(['foo' => 'bar']);
        $accessor = new StdPropertyAccessor(new FakePropertyAccessor());

        $this->assertTrue($accessor->isReadable($object, 'foo'));
        $this->assertFalse($accessor->isReadable($object, 'foz'));
    }

    public function testUsesDecoratedAccessorToDertermineIfPropertyIsReadbleIfObjectIsNotAnStdClassInstance()
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

        $accessor = new StdPropertyAccessor($decoratedAccessor);
        $actual = $accessor->isReadable($object, $property);

        $this->assertEquals($expected, $actual);

        $decoratedAccessorProphecy->isReadable(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
