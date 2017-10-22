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
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\RootResolutionException;
use Nelmio\Alice\Throwable\GenerationThrowable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Instantiator\InstantiatorResolver
 */
class InstantiatorResolverTest extends TestCase
{
    public function testIsAnInstantiator()
    {
        $this->assertTrue(is_a(InstantiatorResolver::class, InstantiatorInterface::class, true));
    }

    public function testIsResolverAware()
    {
        $this->assertEquals(
            new InstantiatorResolver(new FakeInstantiator(), new FakeValueResolver()),
            (new InstantiatorResolver(new FakeInstantiator()))->withValueResolver(new FakeValueResolver())
        );
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(InstantiatorResolver::class))->isCloneable());
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
        $set = ResolvedFixtureSetFactory::create(
            new ParameterBag([
                'ping' => 'pong',
            ])
        );
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
            ->resolve($firstArg, $fixture, $set, ['ping' => 'pong'], $context)
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
            ->resolve($secondArg, $fixture, $setAfterFirstArgResolution, ['ping' => 'pong', 1 => 'resolvedFirstArg'], $context)
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

    public function testThrowsAGenerationThrowableIfResolutionFails()
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
        $fixture = new SimpleFixture('dummy', 'stdClass', $specs);
        $set = ResolvedFixtureSetFactory::create();

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $resolverProphecy
            ->resolve(Argument::cetera())
            ->willThrow(RootResolutionException::class)
        ;
        /** @var ValueResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $instantiator = new InstantiatorResolver(new FakeInstantiator(), $resolver);
        try {
            $instantiator->instantiate($fixture, $set, new GenerationContext());
            $this->fail('Expected exception to be thrown.');
        } catch (GenerationThrowable $throwable) {
            // Expected result.
        }
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
