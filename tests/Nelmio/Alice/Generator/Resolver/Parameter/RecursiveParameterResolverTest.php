<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Parameter;

use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Parameter\RecursiveParameterResolver
 */
class RecursiveParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableParameterResolver()
    {
        $this->assertTrue(is_a(RecursiveParameterResolver::class, ChainableParameterResolverInterface::class, true));
    }

    public function testIsAParameterResolverAwareResolver()
    {
        $this->assertTrue(is_a(RecursiveParameterResolver::class, ParameterResolverAwareInterface::class, true));
    }

    public function testIsImmutable()
    {
        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $decoratedResolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /* @var ChainableParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RecursiveParameterResolver($decoratedResolver);
        $newResolver = $resolver->withResolver($injectedResolver);

        $this->assertInstanceOf(RecursiveParameterResolver::class, $newResolver);
        $this->assertNotSame($resolver, $newResolver);

        $resolver = new RecursiveParameterResolver(new ImmutableDummyChainableResolverAwareResolver());
        $newResolver = $resolver->withResolver($injectedResolver);

        $this->assertInstanceOf(RecursiveParameterResolver::class, $newResolver);
        $this->assertNotSame($resolver, $newResolver);
        $this->assertNotSameDecoratedResolver($resolver, $newResolver);
    }

    public function testIsDeepClonable()
    {
        $resolver = new RecursiveParameterResolver(new ImmutableDummyChainableResolverAwareResolver());
        $newResolver = clone $resolver;

        $this->assertInstanceOf(RecursiveParameterResolver::class, $newResolver);
        $this->assertNotSame($newResolver, $resolver);
        $this->assertNotSameInjectedResolver($newResolver, $resolver);
    }
    
    public function testUseDecoratedResolverToKnowWhichParameterItCanResolve()
    {
        $parameter1 = new Parameter('foo', null);
        $parameter2 = new Parameter('bar', null);
        
        $decoratedResolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $decoratedResolverProphecy->canResolve($parameter1)->willReturn(false);
        $decoratedResolverProphecy->canResolve($parameter2)->willReturn(true);
        /* @var ChainableParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RecursiveParameterResolver($decoratedResolver);
        
        $this->assertFalse($resolver->canResolve($parameter1));
        $this->assertTrue($resolver->canResolve($parameter2));
        
        $decoratedResolverProphecy->canResolve(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    /**
     * @testdox Resolves the given parameter two times with the decorated resolver. If the two results are identical, return this result.
     */
    public function testResolveWithNoChange()
    {
        $parameter = new Parameter('foo', null);
        $unresolvedParameters = new ParameterBag(['name' => 'Alice']);
        $resolvedParameters = new ParameterBag(['place' => 'Wonderlands']);
        $context = new \Nelmio\Alice\Generator\Resolver\ResolvingContext('foo');
        $expected = new ParameterBag(['foo' => 'bar']);

        $decoratedResolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(
                $parameter,
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn($expected)
        ;
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', 'bar'),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn($expected)
        ;
        /* @var ChainableParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RecursiveParameterResolver($decoratedResolver);
        $actual = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        $this->assertEquals($expected, $actual);
        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testResolveIncludesAllResults()
    {
        $parameter = new Parameter('foo', null);
        $unresolvedParameters = new ParameterBag(['name' => 'Alice']);
        $resolvedParameters = new ParameterBag(['place' => 'Wonderlands']);
        $context = new \Nelmio\Alice\Generator\Resolver\ResolvingContext('foo');

        $decoratedResolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(
                $parameter,
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn(
                new ParameterBag([
                    'foo' => 'first result',
                    'another_param1' => 'val1',
                ])
            )
        ;
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', 'first result'),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn(
                new ParameterBag([
                    'foo' => 'second result',
                    'another_param2' => 'val2', // 'another_param1' has already been resolved so is not return in the result set!
                ])
            )
        ;
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', 'second result'),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn(
                new ParameterBag([
                    'foo' => 'second result',   // same as previous
                ])
            )
        ;
        /* @var ChainableParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RecursiveParameterResolver($decoratedResolver);
        $actual = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        $this->assertEquals(
            new ParameterBag([
                'foo' => 'second result',
                'another_param1' => 'val1',
                'another_param2' => 'val2',
            ]),
            $actual
        );
        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(3);
    }

    /**
     * @dataProvider provideContexts
     */
    public function testAlwaysPassContextWhenResolving(ResolvingContext $context = null)
    {
        $parameter = new Parameter('foo', null);

        $decoratedResolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(Argument::any(), Argument::any(), Argument::any(), $context)
            ->willReturn(new ParameterBag(['foo' => null]))
        ;
        /* @var ChainableParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RecursiveParameterResolver($decoratedResolver);
        $resolver->resolve($parameter, new ParameterBag(), new ParameterBag(), $context);

    }

    /**
     * @testdox Resolves the given parameter two times with the decorated resolver. If the two results are identical, return this result.
     */
    public function testResolveWithChange()
    {
        $parameter = new Parameter('foo', null);
        $unresolvedParameters = new ParameterBag(['name' => 'Alice']);
        $resolvedParameters = new ParameterBag(['place' => 'Wonderlands']);
        $context = new ResolvingContext('foo');

        $decoratedResolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(
                $parameter,
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn(
                new ParameterBag(['foo' => 'result1'])
            )
        ;
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', 'result1'),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn(
                new ParameterBag(['foo' => 'result2'])
            )
        ;
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', 'result2'),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn(
                new ParameterBag(['foo' => 'result3'])
            )
        ;
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', 'result3'),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn(
                $expected = new ParameterBag(['foo' => 'result3'])
            )
        ;
        /* @var ChainableParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RecursiveParameterResolver($decoratedResolver);
        $actual = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        $this->assertEquals($expected, $actual);
        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(4);
    }

    public function provideContexts()
    {
        return [
            'no context' => [
                null,
            ],
            'empty context' => [
                new \Nelmio\Alice\Generator\Resolver\ResolvingContext(),
            ],
            'context with random value' => [
                (new \Nelmio\Alice\Generator\Resolver\ResolvingContext())->with('name'),
            ],
        ];
    }

    private function assertNotSameDecoratedResolver(RecursiveParameterResolver $firstResolver, RecursiveParameterResolver $secondResolver)
    {
        $this->assertNotSame(
            $this->getDecoratedResolver($firstResolver),
            $this->getDecoratedResolver($secondResolver)
        );
    }

    private function getDecoratedResolver(RecursiveParameterResolver $resolver): ChainableParameterResolverInterface
    {
        $resolverReflectionObject = new \ReflectionObject($resolver);
        $resolverPropertyReflection = $resolverReflectionObject->getProperty('resolver');
        $resolverPropertyReflection->setAccessible(true);

        return $resolverPropertyReflection->getValue($resolver);
    }

    private function assertNotSameInjectedResolver(RecursiveParameterResolver $firstResolver, RecursiveParameterResolver $secondResolver)
    {
        $this->assertNotSame(
            $this->getInjectedResolver($firstResolver),
            $this->getInjectedResolver($secondResolver)
        );
    }

    private function getInjectedResolver(RecursiveParameterResolver $resolver): ParameterResolverInterface
    {
        $resolverReflectionObject = new \ReflectionObject($resolver);
        $resolverPropertyReflection = $resolverReflectionObject->getProperty('resolver');
        $resolverPropertyReflection->setAccessible(true);

        return $resolverPropertyReflection->getValue($resolver);
    }
}

final class ImmutableDummyChainableResolverAwareResolver implements ChainableParameterResolverInterface, ParameterResolverAwareInterface
{
    public $resolver;

    public function canResolve(Parameter $parameter): bool
    {
        throw new \BadMethodCallException();
    }

    public function withResolver(ParameterResolverInterface $resolver)
    {
        $clone = clone $this;
        $clone->resolver = $resolver;

        return $clone;
    }

    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters
    ): ParameterBag
    {
        throw new \BadMethodCallException();
    }
}

