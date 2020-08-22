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

namespace Nelmio\Alice\Generator\Resolver\Parameter\Chainable;

use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\FakeParameterResolver;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Parameter\Chainable\ArrayParameterResolver
 */
class ArrayParameterResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableParameterResolver(): void
    {
        static::assertTrue(is_a(ArrayParameterResolver::class, ChainableParameterResolverInterface::class, true));
    }

    public function testIsAParameterResolverAwareResolver(): void
    {
        static::assertTrue(is_a(ArrayParameterResolver::class, ParameterResolverAwareInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(ArrayParameterResolver::class))->isCloneable());
    }

    public function testCanBeInstantiatedWithoutAResolver(): void
    {
        new ArrayParameterResolver();
    }

    public function testCanBeInstantiatedWithAResolver(): void
    {
        new ArrayParameterResolver(new FakeParameterResolver());
    }

    public function testWithersReturnANewModifiedInstance(): void
    {
        $propertyRefl = (new ReflectionClass(ArrayParameterResolver::class))->getProperty('resolver');
        $propertyRefl->setAccessible(true);

        $resolver = new ArrayParameterResolver();
        $newResolver = $resolver->withResolver(new FakeParameterResolver());

        static::assertEquals(new ArrayParameterResolver(), $resolver);
        static::assertEquals(new ArrayParameterResolver(new FakeParameterResolver()), $newResolver);
    }

    public function testCanOnlyResolveArrayValues(): void
    {
        $resolver = new ArrayParameterResolver();
        $parameter = new Parameter('foo', null);

        static::assertTrue($resolver->canResolve($parameter->withValue([])));

        static::assertFalse($resolver->canResolve($parameter->withValue(null)));
        static::assertFalse($resolver->canResolve($parameter->withValue(10)));
        static::assertFalse($resolver->canResolve($parameter->withValue(.75)));
        static::assertFalse($resolver->canResolve($parameter->withValue('string')));
        static::assertFalse($resolver->canResolve($parameter->withValue(new stdClass())));
        static::assertFalse($resolver->canResolve($parameter->withValue(function (): void {
        })));
    }

    public function testRequiresInjectedResolverToResolverAParameter(): void
    {
        $resolver = new ArrayParameterResolver();

        $this->expectException(ResolverNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\Generator\Resolver\Parameter\Chainable\ArrayParameterResolver::resolve" to be called only if it has a resolver.');

        $resolver->resolve(new Parameter('foo', null), new ParameterBag(), new ParameterBag());
    }

    public function testIteratesOverEachElementAndUsesTheDecoratedResolverToResolveEachValue(): void
    {
        $parameter = new Parameter(
            'array_param',
            [
                'foo',
                'bar',
            ]
        );

        $unresolvedParameters = new ParameterBag(['name' => 'unresolvedParams']);
        $resolvedParameters = new ParameterBag(['name' => 'resolvedParams']);
        $context = new ResolvingContext();

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $context->add('array_param');
        $injectedResolverProphecy
            ->resolve(
                new Parameter('0', 'foo'),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn(
                new ParameterBag([
                    'name' => 'resolvedParams',
                    '0' => 'val1',
                ])
            )
        ;
        $context->add('array_param');
        $injectedResolverProphecy
            ->resolve(
                new Parameter('1', 'bar'),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            )
            ->willReturn(
                new ParameterBag([
                    'name' => 'resolvedParams',
                    '1' => 'val2',
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new ArrayParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        static::assertEquals(
            new ParameterBag([
                'name' => 'resolvedParams',
                'array_param' => [
                    '0' => 'val1',
                    '1' => 'val2',
                ],
            ]),
            $result
        );
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testIfResolutionResultsInMultipleParametersBeingResolvedThenTheyAreAllIncludedInTheResult(): void
    {
        $array = [
            $val1 = new stdClass(),
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

        static::assertEquals(
            new ParameterBag([
                'array_param' => [
                    '0' => 'val1',
                ],
                'other_param' => 'yo',
            ]),
            $result
        );
    }

    /**
     * @dataProvider provideContexts
     */
    public function testTheContextPassedToTheInjectedResolverIsAlwaysValid(ResolvingContext $context = null, ResolvingContext $expected): void
    {
        $array = [
            $val1 = 'foo',
            $val2 = 'bar',
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
                $expected
            )
            ->willReturn(
                new ParameterBag([
                    'name' => 'resolvedParams',
                    '0' => 'val1',
                ])
            )
        ;
        $injectedResolverProphecy
            ->resolve(
                new Parameter('1', $val2),
                $unresolvedParameters,
                $resolvedParameters,
                $expected
            )
            ->willReturn(
                new ParameterBag([
                    'name' => 'resolvedParams',
                    '1' => 'val2',
                ])
            )
        ;
        /* @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new ArrayParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        static::assertEquals(
            new ParameterBag([
                'name' => 'resolvedParams',
                'array_param' => [
                    '0' => 'val1',
                    '1' => 'val2',
                ],
            ]),
            $result
        );
    }

    public function provideContexts()
    {
        return [
            'no context' => [
                null,
                new ResolvingContext('array_param'),
            ],
            'context that does not contain the parameter being resolved' => [
                new ResolvingContext('unrelated'),
                (function () {
                    $context = new ResolvingContext('unrelated');
                    $context->add('array_param');

                    return $context;
                })(),
            ],
            'context that contains the parameter being resolved' => [
                (function () {
                    $context = new ResolvingContext('unrelated');
                    $context->add('array_param');

                    return $context;
                })(),
                (function () {
                    $context = new ResolvingContext('unrelated');
                    $context->add('array_param');

                    return $context;
                })()
            ],
        ];
    }
}
