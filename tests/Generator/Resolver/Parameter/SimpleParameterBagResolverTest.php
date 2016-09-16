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

use Nelmio\Alice\Generator\Resolver\FakeParameterResolver;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Parameter\SimpleParameterBagResolver
 */
class SimpleParameterBagResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAParameterBagResolver()
    {
        $this->assertTrue(is_a(SimpleParameterBagResolver::class, ParameterBagResolverInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new SimpleParameterBagResolver(new FakeParameterResolver());
    }

    public function testDecoratesResolverToResolveParameterBag()
    {
        $unresolvedParameters = new ParameterBag([
            'foo' => '(unresolved) bar',
            'ping' => '(unresolved) pong',
        ]);

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy
            ->resolve(
                new Parameter('foo', '(unresolved) bar'),
                $unresolvedParameters,
                new ParameterBag(),
                new ResolvingContext('foo')
            )
            ->willReturn(
                $firstResolveResult = new ParameterBag([
                    'foo' => 'bar',
                    'other_param' => 'yo',
                ])
            )
        ;
        $injectedResolverProphecy
            ->resolve(
                new Parameter('ping', '(unresolved) pong'),
                $unresolvedParameters,
                $firstResolveResult,
                new ResolvingContext('ping')
            )
            ->willReturn(
                new ParameterBag([
                    'foo' => 'bar',
                    'other_param' => 'yo',
                    'ping' => 'pong',
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = new SimpleParameterBagResolver($injectedResolver);
        $result = $resolver->resolve($unresolvedParameters);

        $this->assertEquals(
            new ParameterBag([
                'foo' => 'bar',
                'other_param' => 'yo',
                'ping' => 'pong',
            ]),
            $result
        );
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testDoesNotResolveAlreadyResolvedParameters()
    {
        $unresolvedParameters = new ParameterBag([
            'foo' => '(unresolved) bar',
            'ping' => '(unresolved) pong',
        ]);
        $resolvedParameters = new ParameterBag([
            'foo' => 'bar',
            'ping' => 'pong',
        ]);

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = new SimpleParameterBagResolver($injectedResolver);
        $result = $resolver->resolve($unresolvedParameters, $resolvedParameters);

        $this->assertEquals($resolvedParameters, $result);
    }

    public function testResolvesBagWithInjectedParameters()
    {
        $unresolvedParameters = new ParameterBag([
            'foo' => '(unresolved) bar',
            'ping' => '(unresolved) pong',
        ]);
        $injectedParameters = new ParameterBag([
            'other_param' => 'oï',
        ]);

        $decoratedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', '(unresolved) bar'),
                $unresolvedParameters,
                $injectedParameters,
                new ResolvingContext('foo')
            )
            ->willReturn(
                new ParameterBag([
                    'other_param' => 'yo',
                    'foo' => 'bar',
                ])
            )
        ;
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('ping', '(unresolved) pong'),
                $unresolvedParameters,
                new ParameterBag([
                    'other_param' => 'yo',
                    'foo' => 'bar',
                ]),
                new ResolvingContext('ping')
            )
            ->willReturn(
                new ParameterBag([
                    'other_param' => 'yo',
                    'foo' => 'bar',
                    'ping' => 'pong',
                ])
            )
        ;
        /* @var ParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new SimpleParameterBagResolver($decoratedResolver);
        $result = $resolver->resolve($unresolvedParameters, $injectedParameters);

        $this->assertEquals(
            new ParameterBag([
                'other_param' => 'yo',
                'foo' => 'bar',
                'ping' => 'pong',
            ]),
            $result
        );
    }
}
