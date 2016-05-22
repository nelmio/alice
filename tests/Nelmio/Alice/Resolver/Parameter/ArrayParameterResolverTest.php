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
use Nelmio\Alice\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Resolver\ParameterResolverInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Resolver\Parameter\ArrayParameterResolver
 */
class ArrayParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableParameterResolver()
    {
        $this->assertTrue(is_a(ArrayParameterResolver::class, ChainableParameterResolverInterface::class, true));
    }

    public function testIsAParameterResolverAwareResolver()
    {
        $this->assertTrue(is_a(ArrayParameterResolver::class, ParameterResolverAwareInterface::class, true));
    }

    public function testIsImmutable()
    {
        $resolver = new ArrayParameterResolver();

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $newResolver = $resolver->withResolver($injectedResolver);

        $this->assertInstanceOf(ArrayParameterResolver::class, $newResolver);
        $this->assertNotSame($resolver, $newResolver);
    }
    
    public function testCanResolveOnlyArrayValues()
    {
        $resolver = new ArrayParameterResolver();
        $parameter = new Parameter('foo', null);
        
        $this->assertTrue($resolver->canResolve($parameter->withValue([])));

        $this->assertFalse($resolver->canResolve($parameter->withValue(null)));
        $this->assertFalse($resolver->canResolve($parameter->withValue(10)));
        $this->assertFalse($resolver->canResolve($parameter->withValue(.75)));
        $this->assertFalse($resolver->canResolve($parameter->withValue('string')));
        $this->assertFalse($resolver->canResolve($parameter->withValue(new \stdClass())));
        $this->assertFalse($resolver->canResolve($parameter->withValue(function () {})));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Resolver "Nelmio\Alice\Resolver\Parameter\ArrayParameterResolver" must have a resolver
     *                           set before having the method "ArrayParameterResolver::resolve()" called.
     */
    public function testRequiresInjectedResolverToResolverAParameter()
    {
        $resolver = new ArrayParameterResolver();

        $resolver->resolve(new Parameter('foo', null), new ParameterBag(), new ParameterBag());
    }
    
    public function testIterateOverEachElementAndUseTheDecoratedResolverToResolveEachValue()
    {
        $array = [
            $val1 = new \stdClass(),
            $val2 = function () {},
        ];

        $parameter = new Parameter('array_param', $array);

        $unresolvedParameters = new ParameterBag(['name' => 'unresolvedParams']);
        $resolvedParameters = new ParameterBag(['name' => 'resolvedParams']);
        $context = new ResolvingContext();

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy
            ->resolve(
                new Parameter('0', $val1),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn(
                new ParameterBag([
                    '0' => 'val1'
                ])
            )
        ;
        $injectedResolverProphecy
            ->resolve(
                new Parameter('1', $val2),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn(
                new ParameterBag([
                    '1' => 'val2'
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new ArrayParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        $this->assertEquals(
            new ParameterBag([
                'array_param' => [
                    '0' => 'val1',
                    '1' => 'val2',
                ],
            ]),
            $result
        );
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testIncludeAdditionalResolvedParametersInResult()
    {
        $array = [
            $val1 = new \stdClass(),
        ];

        $parameter = new Parameter('array_param', $array);

        $unresolvedParameters = new ParameterBag(['name' => 'unresolvedParams']);
        $resolvedParameters = new ParameterBag(['name' => 'resolvedParams']);
        $context = new ResolvingContext();

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy
            ->resolve(Argument::cetera())
            ->willReturn(
                new ParameterBag([
                    '0' => 'val1',
                    'other_param' => 'yo',
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new ArrayParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        $this->assertEquals(
            new ParameterBag([
                'array_param' => [
                    '0' => 'val1',
                ],
                'other_param' => 'yo',
            ]),
            $result
        );
    }

    public function testIfNoContextIsPassedOneIsCreated()
    {
        $array = [
            $val1 = new \stdClass(),
            $val2 = function () {},
        ];

        $parameter = new Parameter('array_param', $array);

        $unresolvedParameters = new ParameterBag(['name' => 'unresolvedParams']);
        $resolvedParameters = new ParameterBag(['name' => 'resolvedParams']);

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy
            ->resolve(
                new Parameter('0', $val1),
                $unresolvedParameters,
                $resolvedParameters,
                new ResolvingContext('array_param')
            )
            ->willReturn(
                new ParameterBag([
                    '0' => 'val1'
                ])
            )
        ;
        $injectedResolverProphecy
            ->resolve(
                new Parameter('1', $val2),
                $unresolvedParameters,
                $resolvedParameters,
                new ResolvingContext('array_param')
            )
            ->willReturn(
                new ParameterBag([
                    '1' => 'val2'
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new ArrayParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters);

        $this->assertEquals(
            new ParameterBag([
                'array_param' => [
                    '0' => 'val1',
                    '1' => 'val2',
                ],
            ]),
            $result
        );
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }
}
