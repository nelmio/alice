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

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\Caller\FakeCaller;
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\Instantiator\FakeInstantiator;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\Populator\FakePopulator;
use Nelmio\Alice\Generator\PopulatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\ObjectBag;
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
        clone new SimpleObjectGenerator(new FakeInstantiator(), new FakePopulator(), new FakeCaller());
    }

    /**
     * @testdox Do a instantiate-hydrate-calls cycle to generate the object described by the fixture.
     */
    public function testGenerate()
    {
        $fixture = new SimpleFixture('dummy', \stdClass::class, SpecificationBagFactory::create());
        $set = ResolvedFixtureSetFactory::create();
        $instance = new \stdClass();
        $instantiatedObject = new SimpleObject($fixture->getId(), $instance);

        $instantiatorProphecy = $this->prophesize(InstantiatorInterface::class);
        $instantiatorProphecy
            ->instantiate($fixture, $set)
            ->willReturn(
                $setWithInstantiatedObject = ResolvedFixtureSetFactory::create(
                    null,
                    null,
                    (new ObjectBag())->with($instantiatedObject)
                )
            )
        ;
        /** @var InstantiatorInterface $instantiator */
        $instantiator = $instantiatorProphecy->reveal();

        $populatedInstance = clone $instance;
        $populatedInstance->populated = true;

        $populatedObject = new SimpleObject($fixture->getId(), $populatedInstance);

        $populatorProphecy = $this->prophesize(PopulatorInterface::class);
        $populatorProphecy
            ->populate($instantiatedObject, $setWithInstantiatedObject)
            ->willReturn(
                $setWithPopulatedObject = ResolvedFixtureSetFactory::create(
                    null,
                    null,
                    (new ObjectBag())->with($populatedObject)
                )
            )
        ;
        /** @var PopulatorInterface $populator */
        $populator = $populatorProphecy->reveal();

        $instanceAfterCalls = clone $populatedInstance;
        $instanceAfterCalls->calls = true;

        $objectAfterCalls = new SimpleObject($fixture->getId(), $instanceAfterCalls);

        $callerProphecy = $this->prophesize(CallerInterface::class);
        $callerProphecy
            ->doCallsOn($populatedObject, $setWithPopulatedObject)
            ->willReturn(
                $setWithObjectAfterCalls = ResolvedFixtureSetFactory::create(
                    null,
                    null,
                    (new ObjectBag())->with($objectAfterCalls)
                )
            )
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
