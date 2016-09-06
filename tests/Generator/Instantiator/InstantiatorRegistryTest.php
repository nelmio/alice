<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Instantiator;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\ObjectBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\Instantiator\InstantiatorRegistry
 */
class InstantiatorRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAnInstantiator()
    {
        $this->assertTrue(is_a(InstantiatorRegistry::class, InstantiatorInterface::class, true));
    }

    public function testAcceptChainableInstantiators()
    {
        new InstantiatorRegistry([new FakeChainableInstantiator()]);
    }

    /**
     * @expectedException \TypeError
     */
    public function testThrowExceptionIfInvalidParserIsPassed()
    {
        new InstantiatorRegistry([new \stdClass()]);
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new InstantiatorRegistry([]);
    }

    public function testIterateOverEveryInstantiatorAndUseTheFirstValidOne()
    {
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');
        $expected = ResolvedFixtureSetFactory::create(
            null,
            null,
            (new ObjectBag())
                ->with(new SimpleObject('dummy', new \stdClass()))
        );

        $instantiator1Prophecy = $this->prophesize(ChainableInstantiatorInterface::class);
        $instantiator1Prophecy->canInstantiate($fixture)->willReturn(false);
        /* @var ChainableInstantiatorInterface $instantiator1 */
        $instantiator1 = $instantiator1Prophecy->reveal();

        $instantiator2Prophecy = $this->prophesize(ChainableInstantiatorInterface::class);
        $instantiator2Prophecy->canInstantiate($fixture)->willReturn(true);
        $instantiator2Prophecy->instantiate($fixture, $set, $context)->willReturn($expected);
        /* @var ChainableInstantiatorInterface $instantiator2 */
        $instantiator2 = $instantiator2Prophecy->reveal();

        $instantiator3Prophecy = $this->prophesize(ChainableInstantiatorInterface::class);
        $instantiator3Prophecy->canInstantiate(Argument::any())->shouldNotBeCalled();
        /* @var ChainableInstantiatorInterface $instantiator3 */
        $instantiator3 = $instantiator3Prophecy->reveal();

        $registry = new InstantiatorRegistry([
            $instantiator1,
            $instantiator2,
            $instantiator3,
        ]);
        $actual = $registry->instantiate($fixture, $set, $context);

        $this->assertSame($expected, $actual);

        $instantiator1Prophecy->canInstantiate(Argument::any())->shouldHaveBeenCalledTimes(1);
        $instantiator2Prophecy->canInstantiate(Argument::any())->shouldHaveBeenCalledTimes(1);
        $instantiator2Prophecy->instantiate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Instantiator\InstantiatorNotFoundException
     * @expectedExceptionMessage No suitable instantiator found for the fixture "dummy".
     */
    public function testThrowExceptionIfNoSuitableParserIsFound()
    {
        $fixture = new DummyFixture('dummy');

        $set = ResolvedFixtureSetFactory::create();

        $registry = new InstantiatorRegistry([]);
        $registry->instantiate($fixture, $set, new GenerationContext());
    }
}
