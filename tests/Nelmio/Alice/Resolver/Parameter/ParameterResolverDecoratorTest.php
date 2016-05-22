<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver\Parameter;

use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\ParameterBagResolverInterface;
use Nelmio\Alice\Resolver\ParameterResolverInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Resolver\Parameter\ParameterResolverDecorator
 */
class ParameterResolverDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAParameterBagResolver()
    {
        $this->assertTrue(is_a(ParameterResolverDecorator::class, ParameterBagResolverInterface::class, true));
    }

    public function testDecorateResolverToResolveParameterBag()
    {
        $unresolvedParameters = new ParameterBag([
            'foo' => 'unresolved(bar)',
            'ping' => 'unresolved(pong)',
        ]);

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy
            ->resolve(
                new Parameter('foo', 'unresolved(bar)'),
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
                new Parameter('ping', 'unresolved(pong)'),
                $unresolvedParameters,
                $firstResolveResult,
                new ResolvingContext('ping')
            )
            ->willReturn(
                new ParameterBag([
                    'ping' => 'pong',
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = new ParameterResolverDecorator($injectedResolver);
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

    public function testResolveBagWithInjectedParameters()
    {
        $unresolvedParameters = new ParameterBag([
            'foo' => 'unresolved(bar)',
            'ping' => 'unresolved(pong)',
        ]);
        $injectedParameters = new ParameterBag([
            'other_param' => 'oï',
        ]);

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy
            ->resolve(
                new Parameter('foo', 'unresolved(bar)'),
                $unresolvedParameters,
                $injectedParameters,
                new ResolvingContext('foo')
            )
            ->willReturn(
                new ParameterBag([
                    'foo' => 'bar',
                    'other_param' => 'yo',
                ])
            )
        ;
        $injectedResolverProphecy
            ->resolve(
                new Parameter('ping', 'unresolved(pong)'),
                $unresolvedParameters,
                new ParameterBag([
                    'other_param' => 'oï',
                    'foo' => 'bar',
                ]),
                new ResolvingContext('ping')
            )
            ->willReturn(
                new ParameterBag([
                    'ping' => 'pong',
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = new ParameterResolverDecorator($injectedResolver);
        $result = $resolver->resolve($unresolvedParameters, $injectedParameters);

        $this->assertEquals(
            new ParameterBag([
                'other_param' => 'oï',
                'foo' => 'bar',
                'ping' => 'pong',
            ]),
            $result
        );
    }
}
