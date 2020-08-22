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

namespace Nelmio\Alice\Generator\Hydrator\Property;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Dummy as NelmioDummy;
use Nelmio\Alice\Entity\DummyWithDate;
use Nelmio\Alice\Entity\Hydrator\Dummy;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\HydrationException;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\InaccessiblePropertyException;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\InvalidArgumentException;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\NoSuchPropertyException;
use Nelmio\Alice\Throwable\Exception\Symfony\PropertyAccess\RootException as GenericPropertyAccessException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @covers \Nelmio\Alice\Generator\Hydrator\Property\SymfonyPropertyAccessorHydrator
 */
class SymfonyPropertyAccessorHydratorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var SymfonyPropertyAccessorHydrator
     */
    private $hydrator;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    
    protected function setUp(): void
    {
        $this->propertyAccessor = new PropertyAccessor();
        $this->hydrator = new SymfonyPropertyAccessorHydrator($this->propertyAccessor);
    }

    public function testIsAnHydrator(): void
    {
        static::assertTrue(is_a(SymfonyPropertyAccessorHydrator::class, PropertyHydratorInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(SymfonyPropertyAccessorHydrator::class))->isCloneable());
    }

    public function testReturnsHydratedObject(): void
    {
        $property = new Property('username', 'bob');
        $instance = new Dummy();
        $object = new SimpleObject('dummy', $instance);

        $accessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $accessorProphecy->setValue($instance, 'username', 'bob')->willReturn(null);
        /** @var PropertyAccessorInterface $accessor */
        $accessor = $accessorProphecy->reveal();

        $hydrator = new SymfonyPropertyAccessorHydrator($accessor);
        $result = $hydrator->hydrate($object, $property, new GenerationContext());

        static::assertEquals($object, $result);

        $accessorProphecy->setValue(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testThrowsAnHydrationExceptionIfAnAccessExceptionIsThrown(): void
    {
        try {
            $property = new Property('username', 'bob');
            $instance = new Dummy();
            $object = new SimpleObject('dummy', $instance);

            $accessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
            $accessorProphecy->setValue(Argument::cetera())->willThrow(AccessException::class);
            /** @var PropertyAccessorInterface $accessor */
            $accessor = $accessorProphecy->reveal();

            $hydrator = new SymfonyPropertyAccessorHydrator($accessor);
            $hydrator->hydrate($object, $property, new GenerationContext());

            static::fail('Expected exception to be thrown.');
        } catch (InaccessiblePropertyException $exception) {
            static::assertEquals(
                'Could not access to the property "username" of the object "dummy" (class: Nelmio\Alice\Entity\Hydrator\Dummy).',
                $exception->getMessage()
            );
            static::assertEquals(0, $exception->getCode());
            static::assertNotNull($exception->getPrevious());
        }
    }

    public function testThrowsNoPropertyExceptionIfPropertyCouldNotBeFound(): void
    {
        try {
            $object = new SimpleObject('dummy', new NelmioDummy());
            $property = new Property('foo', 'bar');

            $this->hydrator->hydrate($object, $property, new GenerationContext());
            static::fail('Expected exception to be thrown.');
        } catch (NoSuchPropertyException $exception) {
            static::assertEquals(
                'Could not hydrate the property "foo" of the object "dummy" (class: Nelmio\Alice\Dummy).',
                $exception->getMessage()
            );
            static::assertEquals(0, $exception->getCode());
            static::assertNotNull($exception->getPrevious());
        }
    }

    public function testThrowsInvalidArgumentExceptionIfInvalidTypeIsGiven(): void
    {
        try {
            $object = new SimpleObject('dummy', new DummyWithDate());
            $property = new Property('immutableDateTime', 'bar');

            $this->hydrator->hydrate($object, $property, new GenerationContext());

            static::fail('Expected exception to be thrown.');
        } catch (InvalidArgumentException $exception) {
            static::assertEquals(
                'Invalid value given for the property "immutableDateTime" of the object "dummy" (class: Nelmio\Alice\Entity\DummyWithDate).',
                $exception->getMessage()
            );
            static::assertEquals(0, $exception->getCode());
            static::assertNotNull($exception->getPrevious());
        }
    }

    public function testCatchesAnySymfonyPropertyAccessorToThrowAnHydratorException(): void
    {
        try {
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
            $hydrator->hydrate($object, $property, new GenerationContext());

            static::fail('Expected exception to be thrown.');
        } catch (HydrationException $exception) {
            static::assertEquals(
                'Could not hydrate the property "foo" of the object "dummy" (class: Nelmio\Alice\Entity\DummyWithDate).',
                $exception->getMessage()
            );
            static::assertEquals(0, $exception->getCode());
            static::assertNotNull($exception->getPrevious());
        }
    }

    /**
     * @dataProvider provideProperties
     */
    public function testObjectHydrationAgainstMutlipleValues(Property $property): void
    {
        $instance = new Dummy();
        $object = new SimpleObject('dummy', $instance);
        $hydratedObject = $this->hydrator->hydrate($object, $property, new GenerationContext());

        $expected = $property->getValue();
        $actual = $this->propertyAccessor->getValue($hydratedObject->getInstance(), $property->getName());

        static::assertSame($expected, $actual);
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
