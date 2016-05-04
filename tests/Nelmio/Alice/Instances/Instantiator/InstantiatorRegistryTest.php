<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\InstantiatorInterface;
use PhpUnit\PhpUnit;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Instances\Instantiator\InstantiatorRegistry
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class InstantiatorRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function test_is_an_instantiator()
    {
        PhpUnit::assertIsA(InstantiatorInterface::class, InstantiatorRegistry::class);
    }

    public function test_accept_chainable_instantiators()
    {
        $instantiatorProphecy = $this->prophesize(ChainableInstantiatorInterface::class);
        $instantiatorProphecy->canInstantiate(Argument::any())->shouldNotBeCalled();
        /* @var ChainableInstantiatorInterface $instantiator */
        $instantiator = $instantiatorProphecy->reveal();

        new InstantiatorRegistry([$instantiator]);
    }

    public function test_throw_exception_if_invalid_instantiator_is_passed()
    {
        try {
            new InstantiatorRegistry([new \stdClass()]);
        } catch (\InvalidArgumentException $exception) {
            PhpUnit::assertErrorMessageIs(
                'Expected instantiators to be "Nelmio\Alice\Instances\Instantiator\ChainableInstantiatorInterface" '.
                'objects. Instantiator "stdClass" is not.',
                $exception
            );
        }

        try {
            new InstantiatorRegistry([10]);
        } catch (\InvalidArgumentException $exception) {
            PhpUnit::assertErrorMessageIs(
                'Expected instantiators to be "Nelmio\Alice\Instances\Instantiator\ChainableInstantiatorInterface" '.
                'objects. Instantiator "10" is not.',
                $exception
            );
        }
    }

    public function test_iterate_over_every_instantiator_and_use_the_first_valid_one()
    {
        $expected = new \stdClass();

        $fixtureProphecy = $this->prophesize(Fixture::class);
        $fixtureProphecy->getExtensions()->shouldNotBeCalled();
        /* @var Fixture $fixture */
        $fixture = $fixtureProphecy->reveal();

        $instantiator1Prophecy = $this->prophesize(ChainableInstantiatorInterface::class);
        $instantiator1Prophecy->canInstantiate($fixture)->willReturn(false);
        /** @var ChainableInstantiatorInterface $instantiator1 */
        $instantiator1 = $instantiator1Prophecy->reveal();

        $instantiator2Prophecy = $this->prophesize(ChainableInstantiatorInterface::class);
        $instantiator2Prophecy->canInstantiate($fixture)->willReturn(true);
        $instantiator2Prophecy->instantiate($fixture)->willReturn($expected);
        /** @var ChainableInstantiatorInterface $instantiator2 */
        $instantiator2 = $instantiator2Prophecy->reveal();

        $instantiator3Prophecy = $this->prophesize(ChainableInstantiatorInterface::class);
        $instantiator3Prophecy->canInstantiate(Argument::any())->shouldNotBeCalled();
        /** @var ChainableInstantiatorInterface $instantiator3 */
        $instantiator3 = $instantiator3Prophecy->reveal();

        $registry = new InstantiatorRegistry([
            $instantiator1,
            $instantiator2,
            $instantiator3,
        ]);
        $actual = $registry->instantiate($fixture);

        $this->assertSame($expected, $actual);
        
        $instantiator1Prophecy->canInstantiate(Argument::any())->shouldHaveBeenCalledTimes(1);
        $instantiator2Prophecy->canInstantiate(Argument::any())->shouldHaveBeenCalledTimes(1);
        $instantiator2Prophecy->instantiate(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
