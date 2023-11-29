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

namespace Nelmio\Alice\PropertyAccess;

use Nelmio\Alice\Entity\DummyWithPublicProperty;
use Nelmio\Alice\Entity\StdClassFactory;
use Nelmio\Alice\Symfony\PropertyAccess\FakePropertyAccessor;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @covers \Nelmio\Alice\PropertyAccess\StdPropertyAccessor
 * @internal
 */
class StdPropertyAccessorTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAPropertyAccessor(): void
    {
        self::assertTrue(is_a(StdPropertyAccessor::class, PropertyAccessorInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(StdPropertyAccessor::class))->isCloneable());
    }

    public function testSetValueOfAStdClass(): void
    {
        $object = new stdClass();
        $property = 'foo';
        $value = 'bar';

        $expected = StdClassFactory::create(['foo' => 'bar']);

        $accessor = new StdPropertyAccessor(new FakePropertyAccessor());
        $accessor->setValue($object, $property, $value);

        self::assertEquals($expected, $object);
    }

    public function testSetValueWithTheDecoratedAccessorWhenTheObjectIsNotAnInstanceOfStdClass(): void
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
                static function ($args): void {
                    $args[0]->{$args[1]} = $args[2];
                },
            );
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new StdPropertyAccessor($decoratedAccessor);
        $accessor->setValue($object, $property, $value);

        self::assertEquals($expected, $object);

        $decoratedAccessorProphecy->setValue(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testGetValueOfAStdClass(): void
    {
        $object = StdClassFactory::create([$property = 'foo' => $expected = 'bar']);

        $accessor = new StdPropertyAccessor(new FakePropertyAccessor());
        $actual = $accessor->getValue($object, $property);

        self::assertEquals($expected, $actual);
    }

    public function testThrowsAnExceptionIfPropertyNotFoundOnStdClass(): void
    {
        $object = new stdClass();

        $accessor = new StdPropertyAccessor(new FakePropertyAccessor());

        $this->expectException(NoSuchPropertyException::class);
        $this->expectExceptionMessage('Cannot read property "foo" from stdClass.');

        $accessor->getValue($object, 'foo');
    }

    public function testGetValueWithTheDecoratedAccessorWhenTheObjectIsNotAnInstanceOfStdClass(): void
    {
        $object = new DummyWithPublicProperty();
        $property = 'val';
        $object->{$property} = $expected = 'bar';

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->getValue($object, $property)
            ->will(
                static fn ($args) => $args[0]->{$args[1]},
            );
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new StdPropertyAccessor($decoratedAccessor);
        $actual = $accessor->getValue($object, $property);

        self::assertEquals($expected, $actual);

        $decoratedAccessorProphecy->getValue(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testStdClassPropertiesAreAlwaysWriteable(): void
    {
        $object = new stdClass();
        $accessor = new StdPropertyAccessor(new FakePropertyAccessor());

        self::assertTrue($accessor->isWritable($object, 'foo'));
    }

    public function testUsesDecoratedAccessorToDertermineIfPropertyIsWritableIfObjectIsNotAnStdClassInstance(): void
    {
        $object = new DummyWithPublicProperty();
        $property = 'val';

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->isWritable($object, $property)
            ->willReturn($expected = true);
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new StdPropertyAccessor($decoratedAccessor);
        $actual = $accessor->isWritable($object, $property);

        self::assertEquals($expected, $actual);

        $decoratedAccessorProphecy->isWritable(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testStdClassPropertiesAreReadableOnlyIfTheyExists(): void
    {
        $object = StdClassFactory::create(['foo' => 'bar']);
        $accessor = new StdPropertyAccessor(new FakePropertyAccessor());

        self::assertTrue($accessor->isReadable($object, 'foo'));
        self::assertFalse($accessor->isReadable($object, 'foz'));
    }

    public function testUsesDecoratedAccessorToDertermineIfPropertyIsReadbleIfObjectIsNotAnStdClassInstance(): void
    {
        $object = new DummyWithPublicProperty();
        $property = 'val';

        $decoratedAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $decoratedAccessorProphecy
            ->isReadable($object, $property)
            ->willReturn($expected = true);
        /** @var PropertyAccessorInterface $decoratedAccessor */
        $decoratedAccessor = $decoratedAccessorProphecy->reveal();

        $accessor = new StdPropertyAccessor($decoratedAccessor);
        $actual = $accessor->isReadable($object, $property);

        self::assertEquals($expected, $actual);

        $decoratedAccessorProphecy->isReadable(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
