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

namespace Nelmio\Alice\Generator\Instantiator\Chainable;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Dummy;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Instantiator\ChainableInstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Instantiator\Chainable\AbstractChainableInstantiator
 */
class AbstractChainableInstantiatorTest extends TestCase
{
    /**
     * @var AbstractChainableInstantiator
     */
    private $instantiator;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->instantiator = new DummyChainableInstantiator();
    }

    public function testIsAChainableInstantiator()
    {
        $this->assertTrue(is_a(AbstractChainableInstantiator::class, ChainableInstantiatorInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(AbstractChainableInstantiator::class))->isCloneable());
    }

    public function testThrowsExceptionIfCannotCreateInstance()
    {
        try {
            $fixture = new DummyFixture('dummy');
            $set = ResolvedFixtureSetFactory::create();

            $decoratedInstantiatorProphecy = $this->prophesize(AbstractChainableInstantiator::class);
            $decoratedInstantiatorProphecy->createInstance($fixture)->willThrow(\Error::class);
            /** @var AbstractChainableInstantiator $decoratedInstantiator */
            $decoratedInstantiator = $decoratedInstantiatorProphecy->reveal();

            $instantiator = new ProphecyChainableInstantiator($decoratedInstantiator);
            $instantiator->instantiate($fixture, $set, new GenerationContext());

            $this->fail('Expected exception to be thrown.');
        } catch (InstantiationException $exception) {
            $this->assertEquals(
                'Could not instantiate fixture "dummy".',
                $exception->getMessage()
            );
            $this->assertEquals(0, $exception->getCode());
            $this->assertNotNull($exception->getPrevious());
        }
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
     * @expectedExceptionMessage custom exception
     */
    public function testIfCannotCreateInstanceAndExceptionThrownIsAnInstantiationExceptionThenItLetsTheExceptionPass()
    {
        $fixture = new DummyFixture('dummy');
        $set = ResolvedFixtureSetFactory::create();

        $decoratedInstantiatorProphecy = $this->prophesize(AbstractChainableInstantiator::class);
        $decoratedInstantiatorProphecy->createInstance($fixture)->willThrow(new InstantiationException('custom exception'));
        /** @var AbstractChainableInstantiator $decoratedInstantiator */
        $decoratedInstantiator = $decoratedInstantiatorProphecy->reveal();

        $instantiator = new ProphecyChainableInstantiator($decoratedInstantiator);
        $instantiator->instantiate($fixture, $set, new GenerationContext());
    }

    public function testReturnsNewSetWithInstantiatedObject()
    {
        $fixture = new DummyFixture('dummy');
        $set = new ResolvedFixtureSet(
            $parameters = new ParameterBag(['foo' => 'bar']),
            $fixtures = (new FixtureBag())->with(new DummyFixture('another_dummy')),
            $objects = new ObjectBag(['ping' => new Dummy()])
        );

        $instantiatedObject = new \stdClass();
        $instantiatedObject->instantiated = true;

        $decoratedInstantiatorProphecy = $this->prophesize(AbstractChainableInstantiator::class);
        $decoratedInstantiatorProphecy->createInstance($fixture)->willReturn($instantiatedObject);
        /** @var AbstractChainableInstantiator $decoratedInstantiator */
        $decoratedInstantiator = $decoratedInstantiatorProphecy->reveal();

        $expected = new ResolvedFixtureSet(
            $parameters,
            $fixtures,
            $objects->with(new SimpleObject('dummy', $instantiatedObject))
        );

        $instantiator = new ProphecyChainableInstantiator($decoratedInstantiator);
        $actual = $instantiator->instantiate($fixture, $set, new GenerationContext());

        $this->assertEquals($expected, $actual);

        $decoratedInstantiatorProphecy->createInstance(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
