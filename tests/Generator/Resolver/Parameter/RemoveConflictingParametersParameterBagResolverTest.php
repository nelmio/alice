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
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Parameter\RemoveConflictingParametersParameterBagResolver
 * @internal
 */
class RemoveConflictingParametersParameterBagResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAParameterBagResolver(): void
    {
        self::assertTrue(is_a(
            RemoveConflictingParametersParameterBagResolver::class,
            ParameterBagResolverInterface::class,
            true,
        ));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(RemoveConflictingParametersParameterBagResolver::class))->isCloneable());
    }

    public function testRemovesAllConflictingKeysFromInjectedParametersBagBeforeResolvingIt(): void
    {
        $unresolvedParameters = new ParameterBag([
            'foo' => '(unresolved) bar',
            'ping' => '(unresolved) pong',
        ]);
        $injectedParameters = new ParameterBag([
            'foo' => 'bar',
            'foz' => 'baz',
        ]);

        $decoratedResolverProphecy = $this->prophesize(ParameterBagResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(
                $unresolvedParameters,
                new ParameterBag(['foz' => 'baz']),
            )
            ->willReturn(
                $expected = new ParameterBag([
                    'foo' => '(resolved) bar',
                    'ping' => '(resolved) pong',
                    'foz' => 'baz',
                ]),
            );
        /** @var ParameterBagResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RemoveConflictingParametersParameterBagResolver($decoratedResolver);
        $actual = $resolver->resolve($unresolvedParameters, $injectedParameters);

        self::assertEquals($expected, $actual);

        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testCanHandleTheCaseWhereNoParameterIsInjected(): void
    {
        $unresolvedParameters = new ParameterBag([
            'foo' => '(unresolved) bar',
            'ping' => '(unresolved) pong',
        ]);
        $injectedParameters = null;

        $decoratedResolverProphecy = $this->prophesize(ParameterBagResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(
                $unresolvedParameters,
                new ParameterBag(),
            )
            ->willReturn(
                $expected = new ParameterBag([
                    'foo' => '(resolved) bar',
                    'ping' => '(resolved) pong',
                    'foz' => 'baz',
                ]),
            );
        /** @var ParameterBagResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RemoveConflictingParametersParameterBagResolver($decoratedResolver);
        $actual = $resolver->resolve($unresolvedParameters, $injectedParameters);

        self::assertEquals($expected, $actual);

        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
