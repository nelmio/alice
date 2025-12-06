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
use Nelmio\Alice\Throwable\Exception\ParameterNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(StringParameterResolver::class)]
final class StringParameterResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableParameterResolver(): void
    {
        self::assertTrue(is_a(StringParameterResolver::class, ChainableParameterResolverInterface::class, true));
    }

    public function testIsAParameterResolverAwareResolver(): void
    {
        self::assertTrue(is_a(StringParameterResolver::class, ParameterResolverAwareInterface::class, true));
    }

    public function testCanBeInstantiatedWithoutAResolver(): void
    {
        new StringParameterResolver();
    }

    public function testCanBeInstantiatedWithAResolver(): void
    {
        new StringParameterResolver(new FakeParameterResolver());
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(StringParameterResolver::class))->isCloneable());
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $resolver = new StringParameterResolver();
        $newResolver = $resolver->withResolver(new FakeParameterResolver());

        self::assertEquals(new StringParameterResolver(), $resolver);
        self::assertEquals(new StringParameterResolver(new FakeParameterResolver()), $newResolver);
    }

    public function testCanOnlyResolveStringValues(): void
    {
        $resolver = new StringParameterResolver();
        $parameter = new Parameter('foo', null);

        self::assertTrue($resolver->canResolve($parameter->withValue('string')));

        self::assertFalse($resolver->canResolve($parameter->withValue(null)));
        self::assertFalse($resolver->canResolve($parameter->withValue(10)));
        self::assertFalse($resolver->canResolve($parameter->withValue(.75)));
        self::assertFalse($resolver->canResolve($parameter->withValue([])));
        self::assertFalse($resolver->canResolve($parameter->withValue(new stdClass())));
        self::assertFalse($resolver->canResolve($parameter->withValue(
            static function (): void {
            },
        )));
    }

    public function testCanResolveStaticStringsWithoutDecoratedResolver(): void
    {
        $parameter = new Parameter('foo', 'Mad Hatter');
        $expected = new ParameterBag(['foo' => 'Mad Hatter']);

        $resolver = new StringParameterResolver();
        $result = $resolver->resolve($parameter, new ParameterBag(), new ParameterBag());

        self::assertEquals(
            $expected,
            $result,
        );

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /** @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new StringParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, new ParameterBag(), new ParameterBag());

        self::assertEquals(
            $expected,
            $result,
        );
    }

    public function testWhenResolvingDynamicStringLookForResolvedParametersFirst(): void
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

        self::assertEquals(
            $expected,
            $result,
        );

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /** @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new StringParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters);

        self::assertEquals(
            $expected,
            $result,
        );
    }

    public function testChecksIfParameterIsReferencedBeforeTryingToResolveIt(): void
    {
        $parameter = new Parameter('foo', '<{bar}>');
        $unresolvedParameters = new ParameterBag();
        $resolvedParameters = new ParameterBag();

        $resolver = new StringParameterResolver();

        try {
            $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters);
            self::fail('Expected exception to be thrown');
        } catch (ParameterNotFoundException $exception) {
            self::assertEquals(
                'Could not find the parameter "bar" when resolving "foo".',
                $exception->getMessage(),
            );
        }

        $injectedResolverProphecy = $this->prophesize(ParameterResolverInterface::class);
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldNotBeCalled();
        /** @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new StringParameterResolver())->withResolver($injectedResolver);

        try {
            $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters);
            self::fail('Expected exception to be thrown');
        } catch (ParameterNotFoundException $exception) {
            self::assertEquals(
                'Could not find the parameter "bar" when resolving "foo".',
                $exception->getMessage(),
            );
        }
    }

    public function testInjectedResolverToResolveDynamicParameter(): void
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
                (static function () {
                    $context = new ResolvingContext('foo');
                    $context->add('bar');

                    return $context;
                })(),
            )
            ->willReturn(
                new ParameterBag([
                    'random' => 'param',
                    'bar' => 'Mad Hatter',
                ]),
            );
        /** @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new StringParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters);

        self::assertEquals(
            $expected,
            $result,
        );
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testReuseContextIfOneIsFoundWhenResolvingDynamicParameter(): void
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
                $context,
            )
            ->willReturn(
                new ParameterBag([
                    'bar' => 'Mad Hatter',
                ]),
            );
        /** @var ParameterResolverInterface $injectedResolver */
        $injectedResolver = $injectedResolverProphecy->reveal();

        $resolver = (new StringParameterResolver())->withResolver($injectedResolver);
        $result = $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

        self::assertEquals(
            $expected,
            $result,
        );
        $injectedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testThrowsAnExceptionIfNoResolverInjectedWhenRequired(): void
    {
        $parameter = new Parameter('foo', '<{bar}>');
        $unresolvedParameters = new ParameterBag([
            'bar' => 'unresolved(bar)',
        ]);
        $resolvedParameters = new ParameterBag();

        $resolver = new StringParameterResolver();

        $this->expectException(ResolverNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StringParameterResolver::resolveStringKey" to be called only if it has a resolver.');

        $resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters);
    }
}
