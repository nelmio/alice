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

use InvalidArgumentException;
use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\FakeParameterResolver;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\RecursionLimitReachedException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Parameter\Chainable\RecursiveParameterResolver
 * @internal
 */
class RecursiveParameterResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableParameterResolver(): void
    {
        self::assertTrue(is_a(RecursiveParameterResolver::class, ChainableParameterResolverInterface::class, true));
    }

    public function testIsAParameterResolverAwareResolver(): void
    {
        self::assertTrue(is_a(RecursiveParameterResolver::class, ParameterResolverAwareInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(RecursiveParameterResolver::class))->isCloneable());
    }

    public function testThrowsExceptionIfInvalidRecursionLimitGiven(): void
    {
        try {
            new RecursiveParameterResolver(new FakeChainableParameterResolver(), 1);
            self::fail('Expected exception to be thrown.');
        } catch (InvalidArgumentException $exception) {
            self::assertEquals(
                'Expected limit for recursive calls to be of at least 2. Got "1" instead.',
                $exception->getMessage(),
            );
        }
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $resolver = new RecursiveParameterResolver(new DummyChainableParameterResolverAwareResolver());
        $newResolver = $resolver->withResolver(new FakeParameterResolver());

        self::assertEquals(
            new RecursiveParameterResolver(new DummyChainableParameterResolverAwareResolver()),
            $resolver,
        );
        self::assertEquals(
            new RecursiveParameterResolver(new DummyChainableParameterResolverAwareResolver(new FakeParameterResolver())),
            $newResolver,
        );
    }

    public function testUseDecoratedResolverToKnowWhichParameterItCanResolve(): void
    {
        $parameter1 = new Parameter('foo', null);
        $parameter2 = new Parameter('bar', null);

        $decoratedResolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $decoratedResolverProphecy->canResolve($parameter1)->willReturn(false);
        $decoratedResolverProphecy->canResolve($parameter2)->willReturn(true);
        /** @var ChainableParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RecursiveParameterResolver($decoratedResolver);

        self::assertFalse($resolver->canResolve($parameter1));
        self::assertTrue($resolver->canResolve($parameter2));

        $decoratedResolverProphecy->canResolve(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    /**
     * @testdox Resolves the given parameter two times with the decorated resolver. If the two results are identical, return this result
     */
    public function testResolveWithNoChange(): void
    {
        $parameter = new Parameter('foo', null);
        $unresolvedParameters = new ParameterBag(['name' => 'Alice']);
        $resolvedParameters = new ParameterBag(['place' => 'Wonderlands']);
        $context = new ResolvingContext('foo');
        $expected = new ParameterBag(['foo' => 'bar']);

        $decoratedResolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(
                $parameter,
                $unresolvedParameters,
                $resolvedParameters,
                $context,
            )
            ->willReturn($expected);
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', 'bar'),
                $unresolvedParameters,
                $resolvedParameters,
                $context,
            )
            ->willReturn($expected);
        /** @var ChainableParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RecursiveParameterResolver($decoratedResolver);
        $actual = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        self::assertEquals($expected, $actual);
        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }

    public function testIfMultipleParametersAreResolvedInTheProcessThenTheyWillBeIncludedInTheReturnedResult(): void
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
                $context,
            )
            ->willReturn(
                new ParameterBag([
                    'foo' => 'first result',
                    'another_param1' => 'val1',
                ]),
            );
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', 'first result'),
                $unresolvedParameters,
                $resolvedParameters,
                $context,
            )
            ->willReturn(
                new ParameterBag([
                    'foo' => 'second result',
                    'another_param2' => 'val2',
                    // 'another_param1' has already been resolved so is not return in the result set!
                ]),
            );
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', 'second result'),
                $unresolvedParameters,
                $resolvedParameters,
                $context,
            )
            ->willReturn(
                new ParameterBag([
                    'foo' => 'second result',   // same as previous
                ]),
            );
        /** @var ChainableParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RecursiveParameterResolver($decoratedResolver);
        $actual = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        self::assertEquals(
            new ParameterBag([
                'foo' => 'second result',
                'another_param1' => 'val1',
                'another_param2' => 'val2',
            ]),
            $actual,
        );
        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(3);
    }

    /**
     * @dataProvider provideContexts
     */
    public function testTheSameContextIsPassedBetweenEachResolution(?ResolvingContext $context = null): void
    {
        $parameter = new Parameter('foo', null);

        $decoratedResolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(Argument::any(), Argument::any(), Argument::any(), $context)
            ->willReturn(new ParameterBag(['foo' => null]));
        /** @var ChainableParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RecursiveParameterResolver($decoratedResolver);
        $resolver->resolve($parameter, new ParameterBag(), new ParameterBag(), $context);
    }

    /**
     * @testdox Resolves the given parameter two times with the decorated resolver. As the results differ, re-iterate the operation until two successive resolutions leads to the same result.
     */
    public function testResolveWithChange(): void
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
                $context,
            )
            ->willReturn(
                new ParameterBag(['foo' => 'result1']),
            );
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', 'result1'),
                $unresolvedParameters,
                $resolvedParameters,
                $context,
            )
            ->willReturn(
                new ParameterBag(['foo' => 'result2']),
            );
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', 'result2'),
                $unresolvedParameters,
                $resolvedParameters,
                $context,
            )
            ->willReturn(
                new ParameterBag(['foo' => 'result3']),
            );
        $decoratedResolverProphecy
            ->resolve(
                new Parameter('foo', 'result3'),
                $unresolvedParameters,
                $resolvedParameters,
                $context,
            )
            ->willReturn(
                $expected = new ParameterBag(['foo' => 'result3']),
            );
        /** @var ChainableParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RecursiveParameterResolver($decoratedResolver);
        $actual = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        self::assertEquals($expected, $actual);
        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(4);
    }

    public function testThrowsAnExceptionWhenRecursionLimitIsReached(): void
    {
        $parameter = new Parameter('foo', null);
        $unresolvedParameters = new ParameterBag();
        $resolvedParameters = new ParameterBag();
        $context = new ResolvingContext('foo');

        $decoratedResolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(Argument::cetera())
            ->will(
                function ($args) {
                    $hash = spl_object_hash($args[0]);

                    return new ParameterBag(['foo' => uniqid($hash)]);
                },
            );
        /** @var ChainableParameterResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new RecursiveParameterResolver($decoratedResolver);

        try {
            $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);
            self::fail('Expected exception to be thrown.');
        } catch (RecursionLimitReachedException $exception) {
            self::assertEquals(
                'Recursion limit (5 tries) reached while resolving the parameter "foo"',
                $exception->getMessage(),
            );
            $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(5);
        }

        $resolver = new RecursiveParameterResolver($decoratedResolver, 10);

        try {
            $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);
            self::fail('Expected exception to be thrown.');
        } catch (RecursionLimitReachedException $exception) {
            self::assertEquals(
                'Recursion limit (10 tries) reached while resolving the parameter "foo"',
                $exception->getMessage(),
            );
            $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(15);
        }
    }

    public function provideContexts(): iterable
    {
        return [
            'no context' => [
                null,
            ],
            'empty context' => [
                new ResolvingContext(),
            ],
            'context with random value' => [
                (static function () {
                    $context = new ResolvingContext();
                    $context->add('name');

                    return $context;
                })(),
            ],
        ];
    }
}
