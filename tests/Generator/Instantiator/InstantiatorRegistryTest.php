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

namespace Nelmio\Alice\Generator\Instantiator;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\ObjectBag;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\Generator\Instantiator\InstantiatorRegistry
 */
class InstantiatorRegistryTest extends TestCase
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
        new InstantiatorRegistry([new stdClass()]);
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(InstantiatorRegistry::class))->isCloneable());
    }

    public function testPassValueResolverAwarenessPropertyToItsInstantiator()
    {
        $resolver = new FakeValueResolver();

        $registry = new InstantiatorRegistry([]);
        $newRegistry = $registry->withValueResolver($resolver);

        $this->assertEquals(new InstantiatorRegistry([]), $registry);
        $this->assertEquals(new InstantiatorRegistry([], $resolver), $newRegistry);


        $registry = new InstantiatorRegistry([new FakeChainableInstantiator()]);
        $newRegistry = $registry->withValueResolver($resolver);

        $this->assertEquals(new InstantiatorRegistry([new FakeChainableInstantiator()]), $registry);
        $this->assertEquals(
            new InstantiatorRegistry([new FakeChainableInstantiator()], $resolver),
            $newRegistry
        );



        $nonAwareInstantiator = new FakeChainableInstantiator();
        $nonAwareInstantiator->foo = 'bar';

        $instantiatorProphecy = $this->prophesize(ChainableInstantiatorInterface::class);
        $instantiatorProphecy->willImplement(ValueResolverAwareInterface::class);
        $instantiatorProphecy->withValueResolver($resolver)->willReturn(new FakeChainableInstantiator());
        /** @var ChainableInstantiatorInterface $instantiator */
        $instantiator = $instantiatorProphecy->reveal();

        $registry = new InstantiatorRegistry([$nonAwareInstantiator, $instantiator]);
        $newRegistry = $registry->withValueResolver($resolver);

        $this->assertEquals(new InstantiatorRegistry([$nonAwareInstantiator, $instantiator]), $registry);
        $this->assertEquals(
            new InstantiatorRegistry([$nonAwareInstantiator, new FakeChainableInstantiator()], $resolver),
            $newRegistry
        );

        $instantiatorProphecy->withValueResolver(Argument::any())->shouldHaveBeenCalledTimes(1);
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
                ->with(new SimpleObject('dummy', new stdClass()))
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
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiatorNotFoundException
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
