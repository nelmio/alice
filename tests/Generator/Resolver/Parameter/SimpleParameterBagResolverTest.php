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

use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Parameter\SimpleParameterBagResolver
 */
class SimpleParameterBagResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAParameterBagResolver(): void
    {
        static::assertTrue(is_a(SimpleParameterBagResolver::class, ParameterBagResolverInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(SimpleParameterBagResolver::class))->isCloneable());
    }

    public function testDecoratesResolverToResolveParameterBag(): void
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

        static::assertEquals(
            new ParameterBag([
                'foo' => 'bar',
                'other_param' => 'yo',
                'ping' => 'pong',
            ]),
            $result
        );
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testDoesNotResolveAlreadyResolvedParameters(): void
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

        static::assertEquals($resolvedParameters, $result);
    }

    public function testResolvesBagWithInjectedParameters(): void
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

        static::assertEquals(
            new ParameterBag([
                'other_param' => 'yo',
                'foo' => 'bar',
                'ping' => 'pong',
            ]),
            $result
        );
    }
}
