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

use InvalidArgumentException;
use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use ReflectionProperty;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\DynamicArrayValueResolver
 */
class DynamicArrayValueResolverTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ReflectionProperty
     */
    private $resolverRefl;

    
    protected function setUp(): void
    {
        $reflClass = new ReflectionClass(DynamicArrayValueResolver::class);

        $this->resolverRefl = $reflClass->getProperty('resolver');
        $this->resolverRefl->setAccessible(true);
    }

    public function testIsAChainableResolver(): void
    {
        static::assertTrue(is_a(DynamicArrayValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(DynamicArrayValueResolver::class))->isCloneable());
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $resolver = new DynamicArrayValueResolver();
        $newResolver = $resolver->withValueResolver(new FakeValueResolver());

        static::assertEquals(new DynamicArrayValueResolver(), $resolver);
        static::assertEquals(new DynamicArrayValueResolver(new FakeValueResolver()), $newResolver);
    }

    public function testCanResolveDynamicArrayValues(): void
    {
        $resolver = new DynamicArrayValueResolver();

        static::assertTrue($resolver->canResolve(new DynamicArrayValue(1, '')));
        static::assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testCannotResolveValueIfHasNoResolver(): void
    {
        $value = new DynamicArrayValue(1, '');
        $resolver = new DynamicArrayValueResolver();

        $this->expectException(ResolverNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\DynamicArrayValueResolver::resolve" to be called only if it has a resolver.');

        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testIfQuantifierIsAValueThenItWillBeResolvedAsWell(): void
    {
        $quantifier = new FakeValue();
        $value = new DynamicArrayValue($quantifier, '');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();
        $scope = ['injected' => true];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('bar');

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve($quantifier, $fixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(10, ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar'])))
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new DynamicArrayValueResolver($decoratedResolver);
        $resolver->resolve($value, $fixture, $set, $scope, $context);
    }

    public function testThrowsExceptionIfAnInvalidQuantifierIsGiven(): void
    {
        $quantifier = new FakeValue();
        $value = new DynamicArrayValue($quantifier, '');
        $fixture = new DummyFixture('dummy');
        $set = ResolvedFixtureSetFactory::create();
        $scope = ['injected' => true];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('bar');

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve($quantifier, $fixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(-1, ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar'])))
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new DynamicArrayValueResolver($decoratedResolver);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected quantifier to be a positive integer. Got "-1" for "dummy", check you dynamic arrays declarations (e.g. "<numberBetween(1, 2)>x @user*").');

        $resolver->resolve($value, $fixture, $set, $scope, $context);
    }

    public function testDoesNotResolveElementIfIsNotAValue(): void
    {
        $quantifier = new FakeValue();
        $value = new DynamicArrayValue($quantifier, 'static val');
        $fixture = new DummyFixture('dummy');
        $set = ResolvedFixtureSetFactory::create();
        $scope = ['injected' => true];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('bar');

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve($quantifier, $fixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(2, $newSet = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar'])))
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new DynamicArrayValueResolver($decoratedResolver);
        $result = $resolver->resolve($value, $fixture, $set, $scope, $context);

        static::assertSame(['static val', 'static val'], $result->getValue());
        static::assertEquals($newSet, $result->getSet());
    }

    public function testResolvesElementAsManyTimeAsNecessaryIfItIsAValue(): void
    {
        $quantifier = 2;
        $element = new FakeValue();
        $value = new DynamicArrayValue($quantifier, $element);
        $fixture = new DummyFixture('dummy');
        $set = ResolvedFixtureSetFactory::create();
        $scope = ['injected' => true];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('bar');

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $setAfterFirstResolution = ResolvedFixtureSetFactory::create(new ParameterBag(['iteration' => 0]));
        $decoratedResolverProphecy
            ->resolve($element, $fixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(10, $setAfterFirstResolution)
            )
        ;
        $setAfterSecondResolution = ResolvedFixtureSetFactory::create(new ParameterBag(['iteration' => 1]));
        $decoratedResolverProphecy
            ->resolve($element, $fixture, $setAfterFirstResolution, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(100, $setAfterSecondResolution)
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new DynamicArrayValueResolver($decoratedResolver);
        $result = $resolver->resolve($value, $fixture, $set, $scope, $context);

        static::assertSame([10, 100], $result->getValue());
        static::assertEquals($setAfterSecondResolution, $result->getSet());
    }
}
