<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\ObjectGenerator;

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\PopulatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\ObjectGenerator\SimpleObjectGenerator
 */
class SimpleObjectGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAnObjectGenerator()
    {
        $this->assertTrue(is_a(SimpleObjectGenerator::class, ObjectGeneratorInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $instantiatorProphecy = $this->prophesize(InstantiatorInterface::class);
        $instantiatorProphecy->instantiate(Argument::cetera())->shouldNotBeCalled();
        /** @var InstantiatorInterface $instantiator */
        $instantiator = $instantiatorProphecy->reveal();

        $populatorProphecy = $this->prophesize(PopulatorInterface::class);
        $populatorProphecy->populate(Argument::cetera())->shouldNotBeCalled();
        /** @var PopulatorInterface $populator */
        $populator = $populatorProphecy->reveal();

        $callerProphecy = $this->prophesize(CallerInterface::class);
        $callerProphecy->doCallsOn(Argument::any())->shouldNotBeCalled();
        /** @var CallerInterface $caller */
        $caller = $callerProphecy->reveal();

        $generator = new SimpleObjectGenerator($instantiator, $populator, $caller);
        clone $generator;
    }

    public function testGeneratorObject()
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('dummy');
        $fixtureProphecy->getClassName()->willReturn('stdClass');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $set = new ResolvedFixtureSet(
            new ParameterBag(),
            new FixtureBag(),
            new ObjectBag()
        );

        $instance = new \stdClass();

        $instantiatedObjectProphecy = $this->prophesize(ObjectInterface::class);
        $instantiatedObjectProphecy->getReference()->willReturn('dummy');
        $instantiatedObjectProphecy->getInstance()->willReturn($instance);
        /** @var ObjectInterface $instantiatedObject */
        $instantiatedObject = $instantiatedObjectProphecy->reveal();

        $setWithInstantiatedObject = new ResolvedFixtureSet(
            new ParameterBag(),
            new FixtureBag(),
            (new ObjectBag())->with($instantiatedObject)
        );

        $instantiatorProphecy = $this->prophesize(InstantiatorInterface::class);
        $instantiatorProphecy
            ->instantiate($fixture, $set)
            ->willReturn($setWithInstantiatedObject)
        ;
        /** @var InstantiatorInterface $instantiator */
        $instantiator = $instantiatorProphecy->reveal();

        $populatedInstance = clone $instance;
        $populatedInstance->populated = true;

        $populatedObjectProphecy = $this->prophesize(ObjectInterface::class);
        $populatedObjectProphecy->getReference()->willReturn('dummy');
        $populatedObjectProphecy->getInstance()->willReturn($populatedInstance);
        /** @var ObjectInterface $populatedObject */
        $populatedObject = $populatedObjectProphecy->reveal();

        $setWithPopulatedObject = new ResolvedFixtureSet(
            new ParameterBag(),
            new FixtureBag(),
            (new ObjectBag())->with($populatedObject)
        );

        $populatorProphecy = $this->prophesize(PopulatorInterface::class);
        $populatorProphecy
            ->populate($instantiatedObject, $setWithInstantiatedObject)
            ->willReturn($setWithPopulatedObject)
        ;
        /** @var PopulatorInterface $populator */
        $populator = $populatorProphecy->reveal();

        $instanceAfterCalls = clone $populatedInstance;
        $instanceAfterCalls->calls = true;

        $objectAfterCallsProphecy = $this->prophesize(ObjectInterface::class);
        $objectAfterCallsProphecy->getReference()->willReturn('dummy');
        $objectAfterCallsProphecy->getInstance()->willReturn($instanceAfterCalls);
        /** @var ObjectInterface $objectAfterCalls */
        $objectAfterCalls = $objectAfterCallsProphecy->reveal();

        $setWithObjectAfterCalls = new ResolvedFixtureSet(
            new ParameterBag(),
            new FixtureBag(),
            (new ObjectBag())->with($objectAfterCalls)
        );

        $callerProphecy = $this->prophesize(CallerInterface::class);
        $callerProphecy
            ->doCallsOn($populatedObject, $setWithPopulatedObject)
            ->willReturn($setWithObjectAfterCalls)
        ;
        /** @var CallerInterface $caller */
        $caller = $callerProphecy->reveal();

        $generator = new SimpleObjectGenerator($instantiator, $populator, $caller);
        $objects = $generator->generate($fixture, $set);

        $this->assertEquals($setWithObjectAfterCalls->getObjects(), $objects);

        $instantiatorProphecy->instantiate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $populatorProphecy->populate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $callerProphecy->doCallsOn(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
