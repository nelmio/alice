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

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\Hydrator\SimpleHydrator
 */
class SimpleHydratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAnHydrator()
    {
        $this->assertTrue(is_a(SimpleHydrator::class, HydratorInterface::class, true));
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

        $hydrator = new SimpleHydrator(new FakePropertyHydrator());
        $actual = $hydrator->hydrate($object, $set);

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

        $hydratorProphecy = $this->prophesize(PropertyHydratorInterface::class);
        $newInstance = new \stdClass();
        $newInstance->username = 'Bob';
        $newObject = $object->withInstance($newInstance);
        $hydratorProphecy->hydrate($object, $username)->willReturn($newObject);

        $secondNewInstance = clone $newInstance;
        $secondNewInstance->group = 'Badass';
        $secondNewObject = $object->withInstance($secondNewInstance);
        $hydratorProphecy->hydrate($newObject, $group)->willReturn($secondNewObject);
        /** @var PropertyHydratorInterface $hydrator */
        $hydrator = $hydratorProphecy->reveal();

        $expected = new ResolvedFixtureSet(
            new ParameterBag(),
            $fixtures,
            new ObjectBag(['dummy' => $secondNewObject])
        );

        $hydrator = new SimpleHydrator($hydrator);
        $actual = $hydrator->hydrate($object, $set);

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
                            ->with($group = new Property('group', $groupValue = new FakeValue()))
                        ,
                        new MethodCallBag()
                    )
                )
            )
        );

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $setAfterFirstResolution = ResolvedFixtureSetFactory::create(new ParameterBag(['iteration' => 1]), $fixtures);
        $resolverProphecy
            ->resolve($usernameValue, $fixture, $set, [])
            ->willReturn(
                new ResolvedValueWithFixtureSet('Bob', $setAfterFirstResolution)
            )
        ;

        $setAfterSecondResolution = ResolvedFixtureSetFactory::create(new ParameterBag(['iteration' => 2]), $fixtures);
        $resolverProphecy
            ->resolve($groupValue, $fixture, $setAfterFirstResolution, ['username' => 'Bob'])
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
        $hydratorProphecy->hydrate($object, $username->withValue('Bob'))->willReturn($newObject);

        $secondNewInstance = clone $newInstance;
        $secondNewInstance->group = 'Badass';
        $secondNewObject = $object->withInstance($secondNewInstance);
        $hydratorProphecy->hydrate($newObject, $group->withValue('Badass'))->willReturn($secondNewObject);
        /** @var PropertyHydratorInterface $hydrator */
        $hydrator = $hydratorProphecy->reveal();

        $expected = new ResolvedFixtureSet(
            new ParameterBag(['iteration' => 2]),
            $fixtures,
            new ObjectBag(['dummy' => $secondNewObject])
        );

        $hydrator = new SimpleHydrator($hydrator, $resolver);
        $actual = $hydrator->hydrate($object, $set);

        $this->assertEquals($expected, $actual);
    }
}
