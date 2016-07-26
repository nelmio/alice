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
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\Definition\Value\VariableValue;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\Instantiator\InstantiatorResolver
 */
class InstantiatorResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAnInstantiator()
    {
        $this->assertTrue(is_a(InstantiatorRegistry::class, InstantiatorInterface::class, true));
    }
    
    public function testResolvesAllArguments()
    {
        $specs = new SpecificationBag(
            new SimpleMethodCall(
                '__construct',
                [
                    $firstArg = new VariableValue('firstArg'),
                    $secondArg = new VariableValue('secondArg'),
                ]
            ),
            new PropertyBag(),
            new MethodCallBag()
        );
        $resolvedSpecs = $specs->withConstructor(
            new SimpleMethodCall(
                '__construct',
                [
                    'resolvedFirstArg',
                    'resolvedSecondArg',
                ]
            )
        );
        $fixture = new SimpleFixture('dummy', 'stdClass', $specs);
        $set = new ResolvedFixtureSet(new ParameterBag(), new FixtureBag(), new ObjectBag());

        $expected = new ResolvedFixtureSet(
            new ParameterBag(),
            (new FixtureBag())->with($fixture->withSpecs($resolvedSpecs)),
            new ObjectBag(['dummy' => new \stdClass()])
        );

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $setAfterFirstArgResolution = new ResolvedFixtureSet(
            $set->getParameters(),
            (new FixtureBag())->with(new DummyFixture('dummy')),
            $set->getObjects()
        );
        $resolverProphecy
            ->resolve($firstArg, $fixture, $set)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    'resolvedFirstArg',
                    $setAfterFirstArgResolution
                )
            )
        ;
        $setAfterSecondArgResolution = new ResolvedFixtureSet(
            $setAfterFirstArgResolution->getParameters(),
            (new FixtureBag())->with(new DummyFixture('another_dummy')),
            $setAfterFirstArgResolution->getObjects()
        );
        $resolverProphecy
            ->resolve($secondArg, $fixture, $setAfterFirstArgResolution)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    'resolvedSecondArg',
                    $setAfterSecondArgResolution
                )
            )
        ;
        /** @var ValueResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $fixtureAfterResolution = $fixture->withSpecs($resolvedSpecs);
        $decoratedInstantiatorProphecy = $this->prophesize(InstantiatorInterface::class);
        $decoratedInstantiatorProphecy
            ->instantiate($fixtureAfterResolution, $setAfterSecondArgResolution)
            ->willReturn($expected)
        ;
        /** @var InstantiatorInterface $decoratedInstantiator */
        $decoratedInstantiator = $decoratedInstantiatorProphecy->reveal();

        $instantiator = new InstantiatorResolver($resolver, $decoratedInstantiator);
        $actual = $instantiator->instantiate($fixture, $set);

        $this->assertSame($expected, $actual);

        $resolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
        $decoratedInstantiatorProphecy->instantiate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testDoesNotResolveArgumentsIfNoConstructorGiven()
    {
        $specs = new SpecificationBag(
            null,
            new PropertyBag(),
            new MethodCallBag()
        );
        $fixture = new SimpleFixture('dummy', 'stdClass', $specs);
        $set = new ResolvedFixtureSet(new ParameterBag(), new FixtureBag(), new ObjectBag());

        $expected = new ResolvedFixtureSet(
            new ParameterBag(),
            (new FixtureBag())->with($fixture),
            new ObjectBag(['dummy' => new \stdClass()])
        );

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $resolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /** @var ValueResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $decoratedInstantiatorProphecy = $this->prophesize(InstantiatorInterface::class);
        $decoratedInstantiatorProphecy->instantiate($fixture, $set)->willReturn($expected);
        /** @var InstantiatorInterface $decoratedInstantiator */
        $decoratedInstantiator = $decoratedInstantiatorProphecy->reveal();

        $instantiator = new InstantiatorResolver($resolver, $decoratedInstantiator);
        $actual = $instantiator->instantiate($fixture, $set);

        $this->assertSame($expected, $actual);
    }

    public function testDoesNotResolveArgumentsIfSpecifiedNoConstructor()
    {
        $specs = new SpecificationBag(
            new NoMethodCall(),
            new PropertyBag(),
            new MethodCallBag()
        );
        $fixture = new SimpleFixture('dummy', 'stdClass', $specs);
        $set = new ResolvedFixtureSet(new ParameterBag(), new FixtureBag(), new ObjectBag());

        $expected = new ResolvedFixtureSet(
            new ParameterBag(),
            (new FixtureBag())->with($fixture),
            new ObjectBag(['dummy' => new \stdClass()])
        );

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $resolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /** @var ValueResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $decoratedInstantiatorProphecy = $this->prophesize(InstantiatorInterface::class);
        $decoratedInstantiatorProphecy->instantiate($fixture, $set)->willReturn($expected);
        /** @var InstantiatorInterface $decoratedInstantiator */
        $decoratedInstantiator = $decoratedInstantiatorProphecy->reveal();

        $instantiator = new InstantiatorResolver($resolver, $decoratedInstantiator);
        $actual = $instantiator->instantiate($fixture, $set);

        $this->assertSame($expected, $actual);
    }
}
