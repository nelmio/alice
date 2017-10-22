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

namespace Nelmio\Alice\Generator\Resolver\Parameter;

use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\FakeParameterResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StringParameterResolver;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\ParameterNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StringParameterResolver
 */
class StringParameterResolverTest extends TestCase
{
    public function testIsAChainableParameterResolver()
    {
        $this->assertTrue(is_a(StringParameterResolver::class, ChainableParameterResolverInterface::class, true));
    }

    public function testIsAParameterResolverAwareResolver()
    {
        $this->assertTrue(is_a(StringParameterResolver::class, ParameterResolverAwareInterface::class, true));
    }

    public function testCanBeInstantiatedWithoutAResolver()
    {
        new StringParameterResolver();
    }

    public function testCanBeInstantiatedWithAResolver()
    {
        new StringParameterResolver(new FakeParameterResolver());
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(StringParameterResolver::class))->isCloneable());
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $resolver = new StringParameterResolver();
        $newResolver = $resolver->withResolver(new FakeParameterResolver());

        $this->assertEquals(new StringParameterResolver(), $resolver);
        $this->assertEquals(new StringParameterResolver(new FakeParameterResolver()), $newResolver);
    }

    public function testCanOnlyResolveStringValues()
    {
        $resolver = new StringParameterResolver();
        $parameter = new Parameter('foo', null);

        $this->assertTrue($resolver->canResolve($parameter->withValue('string')));

        $this->assertFalse($resolver->canResolve($parameter->withValue(null)));
        $this->assertFalse($resolver->canResolve($parameter->withValue(10)));
        $this->assertFalse($resolver->canResolve($parameter->withValue(.75)));
        $this->assertFalse($resolver->canResolve($parameter->withValue([])));
        $this->assertFalse($resolver->canResolve($parameter->withValue(new \stdClass())));
        $this->assertFalse($resolver->canResolve($parameter->withValue(function () {
        })));
    }

    public function testCanResolveStaticStringsWithoutDecoratedResolver()
    {
        $parameter = new Parameter('foo', 'Mad Hatter');
        $expected = new ParameterBag(['foo' => 'Mad Hatter']);

        $resolver = new StringParameterResolver();
        $result = $resolver->resolve($parameter, new ParameterBag(), new ParameterBag());

        $this->assertEquals(
            $expected,
            $result
        );

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new StringParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, new ParameterBag(), new ParameterBag());

        $this->assertEquals(
            $expected,
            $result
        );
    }

    public function testWhenResolvingDynamicStringLookForResolvedParametersFirst()
    {
        $parameter = new Parameter('foo', '<{bar}>');
        $unresolvedParameters = new ParameterBag();
        $resolvedParameters = new ParameterBag(['bar' => 'Mad Hatter']);
        $expected = new ParameterBag([
            'bar' => 'Mad Hatter',
            'foo' => 'Mad Hatter',
        ]);

        $resolver = new StringParameterResolver();
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters);

        $this->assertEquals(
            $expected,
            $result
        );

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new StringParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters);

        $this->assertEquals(
            $expected,
            $result
        );
    }

    public function testChecksIfParameterIsReferencedBeforeTryingToResolveIt()
    {
        $parameter = new Parameter('foo', '<{bar}>');
        $unresolvedParameters = new ParameterBag();
        $resolvedParameters = new ParameterBag();

        $resolver = new StringParameterResolver();
        try {
            $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters);
            $this->fail('Expected exception to be thrown');
        } catch (ParameterNotFoundException $exception) {
            $this->assertEquals(
                'Could not find the parameter "bar" when resolving "foo".',
                $exception->getMessage()
            );
        }

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new StringParameterResolver())->withResolver($injectedResolver);
        try {
            $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters);
            $this->fail('Expected exception to be thrown');
        } catch (ParameterNotFoundException $exception) {
            $this->assertEquals(
                'Could not find the parameter "bar" when resolving "foo".',
                $exception->getMessage()
            );
        }
    }

    public function testInjectedResolverToResolveDynamicParameter()
    {
        $parameter = new Parameter('foo', '<{bar}>');
        $unresolvedParameters = new ParameterBag([
            'bar' => 'unresolved(bar)',
        ]);
        $resolvedParameters = new ParameterBag([
            'random' => 'param',
        ]);
        $expected = new ParameterBag([
            'random' => 'param',
            'bar' => 'Mad Hatter',
            'foo' => 'Mad Hatter',
        ]);

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy
            ->resolve(
                new Parameter('bar', 'unresolved(bar)'),
                $unresolvedParameters,
                $resolvedParameters,
                (function () {
                    $context = new ResolvingContext('foo');
                    $context->add('bar');

                    return $context;
                })()
            )
            ->willReturn(
                new ParameterBag([
                    'random' => 'param',
                    'bar' => 'Mad Hatter',
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new StringParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters);

        $this->assertEquals(
            $expected,
            $result
        );
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testReuseContextIfOneIsFoundWhenResolvingDynamicParameter()
    {
        $parameter = new Parameter('foo', '<{bar}>');
        $unresolvedParameters = new ParameterBag([
            'bar' => 'unresolved(bar)',
        ]);
        $resolvedParameters = new ParameterBag();
        $context = new ResolvingContext('ping');
        $context->add('foo');
        $expected = new ParameterBag([
            'bar' => 'Mad Hatter',
            'foo' => 'Mad Hatter',
        ]);

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy
            ->resolve(
                new Parameter('bar', 'unresolved(bar)'),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn(
                new ParameterBag([
                    'bar' => 'Mad Hatter',
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new StringParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        $this->assertEquals(
            $expected,
            $result
        );
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StringParameterResolver::resolveStringKey" to be called only if it has a resolver.
     */
    public function testThrowsAnExceptionIfNoResolverInjectedWhenRequired()
    {
        $parameter = new Parameter('foo', '<{bar}>');
        $unresolvedParameters = new ParameterBag([
            'bar' => 'unresolved(bar)',
        ]);
        $resolvedParameters = new ParameterBag();

        $resolver = new StringParameterResolver();
        $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters);
    }
}
