<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Populator;

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\Hydrator\FakeHydrator;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\Generator\PopulatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\Populator\SimplePopulator
 */
class SimplePopulatorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAPopulator()
    {
        $this->assertTrue(is_a(SimplePopulator::class, PopulatorInterface::class, true));
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

        $populator = new SimplePopulator(new FakeValueResolver(), new FakeHydrator());
        $actual = $populator->populate($object, $set);

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

        $hydratorProphecy = $this->prophesize(HydratorInterface::class);
        $newInstance = new \stdClass();
        $newInstance->username = 'Bob';
        $newObject = $object->withInstance($newInstance);
        $hydratorProphecy->hydrate($object, $username)->willReturn($newObject);

        $secondNewInstance = clone $newInstance;
        $secondNewInstance->group = 'Badass';
        $secondNewObject = $object->withInstance($secondNewInstance);
        $hydratorProphecy->hydrate($newObject, $group)->willReturn($secondNewObject);
        /** @var HydratorInterface $hydrator */
        $hydrator = $hydratorProphecy->reveal();

        $expected = new ResolvedFixtureSet(
            new ParameterBag(),
            $fixtures,
            new ObjectBag(['dummy' => $secondNewObject])
        );

        $populator = new SimplePopulator(new FakeValueResolver(), $hydrator);
        $actual = $populator->populate($object, $set);

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

        $hydratorProphecy = $this->prophesize(HydratorInterface::class);
        $newInstance = new \stdClass();
        $newInstance->username = 'Bob';
        $newObject = $object->withInstance($newInstance);
        $hydratorProphecy->hydrate($object, $username->withValue('Bob'))->willReturn($newObject);

        $secondNewInstance = clone $newInstance;
        $secondNewInstance->group = 'Badass';
        $secondNewObject = $object->withInstance($secondNewInstance);
        $hydratorProphecy->hydrate($newObject, $group->withValue('Badass'))->willReturn($secondNewObject);
        /** @var HydratorInterface $hydrator */
        $hydrator = $hydratorProphecy->reveal();

        $expected = new ResolvedFixtureSet(
            new ParameterBag(['iteration' => 2]),
            $fixtures,
            new ObjectBag(['dummy' => $secondNewObject])
        );

        $populator = new SimplePopulator($resolver, $hydrator);
        $actual = $populator->populate($object, $set);

        $this->assertEquals($expected, $actual);
    }
}
