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

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Error;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\MutableValue;
use Nelmio\Alice\Entity\DummyWithGetter;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\NoSuchMethodException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(FixtureMethodCallReferenceResolver::class)]
final class FixtureMethodCallReferenceResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableResolver(): void
    {
        self::assertTrue(is_a(FixtureMethodCallReferenceResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(FixtureMethodCallReferenceResolver::class))->isCloneable());
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $resolver = new FixtureMethodCallReferenceResolver();
        $newResolver = $resolver->withValueResolver(new FakeValueResolver());

        self::assertEquals(new FixtureMethodCallReferenceResolver(), $resolver);
        self::assertEquals(new FixtureMethodCallReferenceResolver(new FakeValueResolver()), $newResolver);
    }

    public function testCanResolveMethodCallReferenceValues(): void
    {
        $resolver = new FixtureMethodCallReferenceResolver();

        self::assertTrue($resolver->canResolve(new FixtureMethodCallValue(new FakeValue(), new FunctionCallValue('method'))));
        self::assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testCannotResolveValueIfHasNoResolver(): void
    {
        $value = new FixtureMethodCallValue(new FakeValue(), new FunctionCallValue('method'));
        $resolver = new FixtureMethodCallReferenceResolver();

        $this->expectException(ResolverNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureMethodCallReferenceResolver::resolve" to be called only if it has a resolver.');

        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testReturnsSetWithResolvedValue(): void
    {
        $value = new FixtureMethodCallValue(
            $reference = new FakeValue(),
            new FunctionCallValue('getFoo', [
                $arg1 = new FakeValue(),
            ]),
        );
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']));
        $scope = ['val' => 'scopie'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $valueResolverContext = new GenerationContext();
        $valueResolverContext->markIsResolvingFixture('foo');
        $valueResolverContext->markAsNeedsCompleteGeneration();

        $dummyProphecy = $this->prophesize(DummyWithGetter::class);
        $dummyProphecy->getFoo('resolved_argument')
            ->shouldBeCalled()
            ->willReturn('resolved_value');

        /** @var DummyWithGetter $dummy */
        $dummy = $dummyProphecy->reveal();

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve($arg1, $fixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    'resolved_argument',
                    $newSet = ResolvedFixtureSetFactory::create(new ParameterBag(['ping' => 'pong'])),
                ),
            );
        $valueResolverProphecy
            ->resolve($reference, $fixture, $newSet, $scope, $valueResolverContext)
            ->willReturn(
                new ResolvedValueWithFixtureSet($dummy, $newSet),
            );
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $expected = new ResolvedValueWithFixtureSet('resolved_value', $newSet);

        $resolver = new FixtureMethodCallReferenceResolver($valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        self::assertEquals($expected, $actual);

        $valueResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
        $dummyProphecy->getFoo(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testThrowsAResolverExceptionOnError(): void
    {
        try {
            $value = new FixtureMethodCallValue(
                $reference = new MutableValue(new DummyWithGetter()),
                new FunctionCallValue('getFoo'),
            );
            $set = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']));

            $error = new Error();
            $dummyProphecy = $this->prophesize(DummyWithGetter::class);
            $dummyProphecy->getFoo()->will(
                function () use ($error): void {
                    throw $error;
                },
            );

            $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
            $valueResolverProphecy
                ->resolve($reference, Argument::cetera())
                ->willReturn(
                    new ResolvedValueWithFixtureSet(
                        $instance = $dummyProphecy->reveal(),
                        $newSet = ResolvedFixtureSetFactory::create(new ParameterBag()),
                    ),
                );
            /** @var ValueResolverInterface $valueResolver */
            $valueResolver = $valueResolverProphecy->reveal();

            $resolver = new FixtureMethodCallReferenceResolver($valueResolver);
            $resolver->resolve($value, new FakeFixture(), $set, [], new GenerationContext());

            self::fail('Expected exception to be thrown.');
        } catch (UnresolvableValueException $exception) {
            self::assertEquals(
                'Could not resolve value "mutable->getFoo()".',
                $exception->getMessage(),
            );
            self::assertEquals(0, $exception->getCode());
            self::assertSame($error, $exception->getPrevious());
        }
    }

    public function testThrowsAnExceptionIfResolvedReferenceHasNoSuchMethod(): void
    {
        try {
            $instance = new DummyWithGetter();
            $value = new FixtureMethodCallValue(
                new MutableValue($instance),
                new FunctionCallValue('getNonExistent'),
            );

            $set = ResolvedFixtureSetFactory::create();

            $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
            $valueResolverProphecy
                ->resolve(Argument::cetera())
                ->willReturn(
                    new ResolvedValueWithFixtureSet(new stdClass(), $set),
                );
            /** @var ValueResolverInterface $valueResolver */
            $valueResolver = $valueResolverProphecy->reveal();

            $resolver = new FixtureMethodCallReferenceResolver($valueResolver);
            $resolver->resolve(
                $value,
                new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create()),
                $set,
                [],
                new GenerationContext(),
            );

            self::fail('Expected exception to be thrown.');
        } catch (NoSuchMethodException $exception) {
            self::assertEquals(
                'Could not find the method "getNonExistent" of the object "dummy" (class: Dummy).',
                $exception->getMessage(),
            );
            self::assertEquals(0, $exception->getCode());
            self::assertNull($exception->getPrevious());
        }
    }
}
