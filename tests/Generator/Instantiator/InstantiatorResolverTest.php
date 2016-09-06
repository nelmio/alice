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
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Definition\Value\VariableValue;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\Instantiator\InstantiatorResolver
 */
class InstantiatorResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAnInstantiator()
    {
        $this->assertTrue(is_a(InstantiatorResolver::class, InstantiatorInterface::class, true));
    }

    public function testIsResolverAware()
    {
        $this->assertEquals(
            new InstantiatorResolver(new FakeInstantiator(), new FakeValueResolver()),
            (new InstantiatorResolver(new FakeInstantiator()))->withResolver(new FakeValueResolver())
        );
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new InstantiatorResolver(new FakeInstantiator(), new FakeValueResolver());
    }

    public function testResolvesAllArguments()
    {
        $specs = SpecificationBagFactory::create(
            new SimpleMethodCall(
                '__construct',
                [
                    $firstArg = new VariableValue('firstArg'),
                    $secondArg = new VariableValue('secondArg'),
                ]
            )
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
        $set = ResolvedFixtureSetFactory::create();
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $expected = ResolvedFixtureSetFactory::create(
            null,
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
            ->resolve($firstArg, $fixture, $set, [], $context)
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
            ->resolve($secondArg, $fixture, $setAfterFirstArgResolution, [], $context)
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
            ->instantiate($fixtureAfterResolution, $setAfterSecondArgResolution, $context)
            ->willReturn($expected)
        ;
        /** @var InstantiatorInterface $decoratedInstantiator */
        $decoratedInstantiator = $decoratedInstantiatorProphecy->reveal();

        $instantiator = new InstantiatorResolver($decoratedInstantiator, $resolver);
        $actual = $instantiator->instantiate($fixture, $set, $context);

        $this->assertSame($expected, $actual);

        $resolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
        $decoratedInstantiatorProphecy->instantiate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testDoesNotResolveArgumentsIfNoConstructorGiven()
    {
        $specs = SpecificationBagFactory::create();
        $fixture = new SimpleFixture('dummy', 'stdClass', $specs);
        $set = ResolvedFixtureSetFactory::create();
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $expected = ResolvedFixtureSetFactory::create(
            null,
            (new FixtureBag())->with($fixture),
            new ObjectBag(['dummy' => new \stdClass()])
        );

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $resolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /** @var ValueResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $decoratedInstantiatorProphecy = $this->prophesize(InstantiatorInterface::class);
        $decoratedInstantiatorProphecy->instantiate($fixture, $set, $context)->willReturn($expected);
        /** @var InstantiatorInterface $decoratedInstantiator */
        $decoratedInstantiator = $decoratedInstantiatorProphecy->reveal();

        $instantiator = new InstantiatorResolver($decoratedInstantiator, $resolver);
        $actual = $instantiator->instantiate($fixture, $set, $context);

        $this->assertSame($expected, $actual);
    }

    public function testDoesNotResolveArgumentsIfSpecifiedNoConstructor()
    {
        $specs = SpecificationBagFactory::create();
        $fixture = new SimpleFixture('dummy', 'stdClass', $specs);
        $set = ResolvedFixtureSetFactory::create();
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $expected = ResolvedFixtureSetFactory::create(
            null,
            (new FixtureBag())->with($fixture),
            new ObjectBag(['dummy' => new \stdClass()])
        );

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $resolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /** @var ValueResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $decoratedInstantiatorProphecy = $this->prophesize(InstantiatorInterface::class);
        $decoratedInstantiatorProphecy->instantiate($fixture, $set, $context)->willReturn($expected);
        /** @var InstantiatorInterface $decoratedInstantiator */
        $decoratedInstantiator = $decoratedInstantiatorProphecy->reveal();

        $instantiator = new InstantiatorResolver($decoratedInstantiator, $resolver);
        $actual = $instantiator->instantiate($fixture, $set, $context);

        $this->assertSame($expected, $actual);
    }
}
