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

namespace Nelmio\Alice\Generator\Hydrator;

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\Definition\Value\FakeObject;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\RootResolutionException;
use Nelmio\Alice\Throwable\GenerationThrowable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\Generator\Hydrator\SimpleHydrator
 */
class SimpleHydratorTest extends TestCase
{
    public function testIsAnHydrator()
    {
        $this->assertTrue(is_a(SimpleHydrator::class, HydratorInterface::class, true));
    }

    public function testIsValueResolverAware()
    {
        $this->assertEquals(
            (new SimpleHydrator(new FakePropertyHydrator()))->withValueResolver(new FakeValueResolver()),
            new SimpleHydrator(new FakePropertyHydrator(), new FakeValueResolver())
        );
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Hydrator\SimpleHydrator::hydrate" to be called only if it has a resolver.
     */
    public function testThrowsAnExceptionIfDoesNotHaveAResolver()
    {
        $hydrator = new SimpleHydrator(new FakePropertyHydrator());
        $hydrator->hydrate(new FakeObject(), ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testAddsObjectToFixtureSet()
    {
        $object = new SimpleObject('dummy', new \stdClass());
        $set = ResolvedFixtureSetFactory::create(
            null,
            $fixtures = (new FixtureBag())->with(
                new SimpleFixture(
                    'dummy',
                    \stdClass::class,
                    new SpecificationBag(
                        null,
                        new PropertyBag(),
                        new MethodCallBag()
                    )
                )
            )
        );
        $expected = new ResolvedFixtureSet(
            new ParameterBag(),
            $fixtures,
            new ObjectBag(['dummy' => $object])
        );

        $hydrator = new SimpleHydrator(new FakePropertyHydrator(), new FakeValueResolver());
        $actual = $hydrator->hydrate($object, $set, new GenerationContext());

        $this->assertEquals($expected, $actual);
    }

    public function testHydratesObjectWithTheGivenProperties()
    {
        $object = new SimpleObject('dummy', new \stdClass());
        $set = ResolvedFixtureSetFactory::create(
            null,
            $fixtures = (new FixtureBag())->with(
                new SimpleFixture(
                    'dummy',
                    \stdClass::class,
                    new SpecificationBag(
                        null,
                        (new PropertyBag())
                            ->with($username = new Property('username', 'Bob'))
                            ->with($group = new Property('group', 'Badass')),
                        new MethodCallBag()
                    )
                )
            )
        );
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $hydratorProphecy = $this->prophesize(PropertyHydratorInterface::class);
        $newInstance = new \stdClass();
        $newInstance->username = 'Bob';
        $newObject = $object->withInstance($newInstance);
        $hydratorProphecy->hydrate($object, $username, $context)->willReturn($newObject);

        $secondNewInstance = clone $newInstance;
        $secondNewInstance->group = 'Badass';
        $secondNewObject = $object->withInstance($secondNewInstance);
        $hydratorProphecy->hydrate($newObject, $group, $context)->willReturn($secondNewObject);
        /** @var PropertyHydratorInterface $hydrator */
        $hydrator = $hydratorProphecy->reveal();

        $expected = new ResolvedFixtureSet(
            new ParameterBag(),
            $fixtures,
            new ObjectBag(['dummy' => $secondNewObject])
        );

        $hydrator = new SimpleHydrator($hydrator, new FakeValueResolver());
        $actual = $hydrator->hydrate($object, $set, $context);

        $this->assertEquals($expected, $actual);

        $hydratorProphecy->hydrate(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testResolvesAllPropertyValues()
    {
        $object = new SimpleObject('dummy', new \stdClass());
        $set = ResolvedFixtureSetFactory::create(
            null,
            $fixtures = (new FixtureBag())->with(
                $fixture = new SimpleFixture(
                    'dummy',
                    \stdClass::class,
                    new SpecificationBag(
                        null,
                        (new PropertyBag())
                            ->with($username = new Property('username', $usernameValue = new FakeValue()))
                            ->with($group = new Property('group', $groupValue = new FakeValue())),
                        new MethodCallBag()
                    )
                )
            )
        );
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $setAfterFirstResolution = ResolvedFixtureSetFactory::create(new ParameterBag(['iteration' => 1]), $fixtures);
        $resolverProphecy
            ->resolve(
                $usernameValue,
                $fixture,
                $set,
                [
                    '_instances' => $set->getObjects()->toArray(),
                ],
                $context
            )
            ->willReturn(
                new ResolvedValueWithFixtureSet('Bob', $setAfterFirstResolution)
            )
        ;

        $setAfterSecondResolution = ResolvedFixtureSetFactory::create(new ParameterBag(['iteration' => 2]), $fixtures);
        $resolverProphecy
            ->resolve(
                $groupValue,
                $fixture,
                $setAfterFirstResolution,
                [
                    '_instances' => $set->getObjects()->toArray(),
                    'username' => 'Bob',
                ],
                $context
            )
            ->willReturn(
                new ResolvedValueWithFixtureSet('Badass', $setAfterSecondResolution)
            )
        ;
        /** @var ValueResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $hydratorProphecy = $this->prophesize(PropertyHydratorInterface::class);
        $newInstance = new \stdClass();
        $newInstance->username = 'Bob';
        $newObject = $object->withInstance($newInstance);
        $hydratorProphecy->hydrate($object, $username->withValue('Bob'), $context)->willReturn($newObject);

        $secondNewInstance = clone $newInstance;
        $secondNewInstance->group = 'Badass';
        $secondNewObject = $object->withInstance($secondNewInstance);
        $hydratorProphecy->hydrate($newObject, $group->withValue('Badass'), $context)->willReturn($secondNewObject);
        /** @var PropertyHydratorInterface $hydrator */
        $hydrator = $hydratorProphecy->reveal();

        $expected = new ResolvedFixtureSet(
            new ParameterBag(['iteration' => 2]),
            $fixtures,
            new ObjectBag(['dummy' => $secondNewObject])
        );

        $hydrator = new SimpleHydrator($hydrator, $resolver);
        $actual = $hydrator->hydrate($object, $set, $context);

        $this->assertEquals($expected, $actual);
    }

    public function testThrowsAGenerationThrowableIfResolutionFails()
    {
        $object = new SimpleObject('dummy', new \stdClass());
        $set = ResolvedFixtureSetFactory::create(
            null,
            $fixtures = (new FixtureBag())->with(
                $fixture = new SimpleFixture(
                    'dummy',
                    \stdClass::class,
                    new SpecificationBag(
                        null,
                        (new PropertyBag())
                            ->with(new Property('username', $usernameValue = new FakeValue()))
                            ->with(new Property('group', $groupValue = new FakeValue())),
                        new MethodCallBag()
                    )
                )
            )
        );

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $resolverProphecy
            ->resolve(Argument::cetera())
            ->willThrow(RootResolutionException::class)
        ;
        /** @var ValueResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $hydrator = new SimpleHydrator(new FakePropertyHydrator(), $resolver);
        try {
            $hydrator->hydrate($object, $set, new GenerationContext());
            $this->fail('Expected exception to be thrown.');
        } catch (GenerationThrowable $throwable) {
            // Expected result
        }
    }
}
