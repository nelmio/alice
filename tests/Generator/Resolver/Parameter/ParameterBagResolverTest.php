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
use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Parameter\ParameterBagResolver
 */
class ParameterBagResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAParameterBagResolver()
    {
        $this->assertTrue(is_a(ParameterBagResolver::class, ParameterBagResolverInterface::class, true));
    }

    public function testIsDeepClonable()
    {
        $resolver = new ParameterBagResolver(new DummyParameterResolverInterface());
        $newResolver = clone $resolver;

        $this->assertInstanceOf(ParameterBagResolver::class, $newResolver);
        $this->assertNotSame($newResolver, $resolver);
        $this->assertNotSameInjectedResolver($newResolver, $resolver);
    }
    
    public function testDecoratesResolverToResolveParameterBag()
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
                    'foo' => 'bar',
                    'other_param' => 'yo',
                    'ping' => 'pong',
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = new ParameterBagResolver($injectedResolver);
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
            'foo' => 'unresolved(bar)',
            'ping' => 'unresolved(pong)',
        ]);
        $resolvedParameters = new ParameterBag([
            'foo' => 'bar',
            'ping' => 'pong',
        ]);

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = new ParameterBagResolver($injectedResolver);
        $result = $resolver->resolve($unresolvedParameters, $resolvedParameters);

        $this->assertEquals($resolvedParameters, $result);
    }

    public function testResolvesBagWithInjectedParameters()
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
                    'other_param' => 'yo',
                    'foo' => 'bar',
                ])
            )
        ;
        $injectedResolverProphecy
            ->resolve(
                new Parameter('ping', 'unresolved(pong)'),
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
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = new ParameterBagResolver($injectedResolver);
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

    private function assertNotSameInjectedResolver(ParameterBagResolver $firstResolver, ParameterBagResolver $secondResolver)
    {
        $this->assertNotSame(
            $this->getInjectedResolver($firstResolver),
            $this->getInjectedResolver($secondResolver)
        );
    }

    private function getInjectedResolver(ParameterBagResolver $resolver): ParameterResolverInterface
    {
        $resolverReflectionObject = new \ReflectionObject($resolver);
        $resolverPropertyReflection = $resolverReflectionObject->getProperty('resolver');
        $resolverPropertyReflection->setAccessible(true);

        return $resolverPropertyReflection->getValue($resolver);
    }
}
