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

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\FakeObjectGenerator;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeChainableValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\ObjectBag;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\SelfFixtureReferenceResolver
 * @internal
 */
class SelfFixtureReferenceResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableResolver(): void
    {
        self::assertTrue(is_a(SelfFixtureReferenceResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsObjectGeneratorAware(): void
    {
        self::assertTrue(is_a(SelfFixtureReferenceResolver::class, ObjectGeneratorAwareInterface::class, true));
    }

    public function testIsValueResolverAware(): void
    {
        self::assertTrue(is_a(SelfFixtureReferenceResolver::class, ValueResolverAwareInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(SelfFixtureReferenceResolver::class))->isCloneable());
    }

    public function testCanResolveTheValueResolvableByItsDecoratedResolver(): void
    {
        $value = new FakeValue();

        $decoratedResolverProphecy = $this->prophesize(ChainableValueResolverInterface::class);
        $decoratedResolverProphecy->canResolve($value)->willReturn(true);
        /** @var ChainableValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new SelfFixtureReferenceResolver($decoratedResolver);

        self::assertTrue($resolver->canResolve($value));

        $decoratedResolverProphecy->canResolve(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testPassesTheObjectGeneratorAwarenessPropertyToItsDecoratedResolver(): void
    {
        $generator = new FakeObjectGenerator();

        $resolver = new SelfFixtureReferenceResolver(new FakeChainableValueResolver());
        $newResolver = $resolver->withObjectGenerator($generator);

        self::assertEquals($newResolver, $resolver);
        self::assertNotSame($newResolver, $resolver);

        $decoratedResolverProphecy = $this->prophesize(ChainableValueResolverInterface::class);
        $decoratedResolverProphecy->willImplement(ObjectGeneratorAwareInterface::class);
        $decoratedResolverProphecy
            ->withObjectGenerator($generator)
            ->willReturn($newDecoratedResolver = new FakeChainableValueResolver());
        /** @var ChainableValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new SelfFixtureReferenceResolver($decoratedResolver);
        $newResolver = $resolver->withObjectGenerator($generator);

        self::assertEquals(new SelfFixtureReferenceResolver($decoratedResolver), $resolver);
        self::assertEquals(new SelfFixtureReferenceResolver($newDecoratedResolver), $newResolver);

        $decoratedResolverProphecy->withObjectGenerator(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testPassesTheValeResolverAwarenessPropertyToItsDecoratedResolver(): void
    {
        $valueResolver = new FakeValueResolver();

        $resolver = new SelfFixtureReferenceResolver(new FakeChainableValueResolver());
        $newResolver = $resolver->withValueResolver($valueResolver);

        self::assertEquals($newResolver, $resolver);
        self::assertNotSame($newResolver, $resolver);

        $decoratedResolverProphecy = $this->prophesize(ChainableValueResolverInterface::class);
        $decoratedResolverProphecy->willImplement(ValueResolverAwareInterface::class);
        $decoratedResolverProphecy
            ->withValueResolver($valueResolver)
            ->willReturn($newDecoratedResolver = new FakeChainableValueResolver());
        /** @var ChainableValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new SelfFixtureReferenceResolver($decoratedResolver);
        $newResolver = $resolver->withValueResolver($valueResolver);

        self::assertEquals(new SelfFixtureReferenceResolver($decoratedResolver), $resolver);
        self::assertEquals(new SelfFixtureReferenceResolver($newDecoratedResolver), $newResolver);

        $decoratedResolverProphecy->withValueResolver(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testCanResolveValuesOfItsDecoratedResolver(): void
    {
        $value = new FakeValue();

        $decoratedResolverProphecy = $this->prophesize(ChainableValueResolverInterface::class);
        $decoratedResolverProphecy->canResolve($value)->willReturn(true);
        /** @var ChainableValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new SelfFixtureReferenceResolver($decoratedResolver);

        self::assertTrue($resolver->canResolve($value));
        $decoratedResolverProphecy->canResolve(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testReturnsTheFixtureBeingResolvedAsTheResolvedValueIfTheReferenceMatchesSelf(): void
    {
        $valueProphecy = $this->prophesize(ValueInterface::class);
        $valueProphecy->getValue()->willReturn('self');
        /** @var ValueInterface $value */
        $value = $valueProphecy->reveal();

        $expectedObject = new stdClass();
        $expectedObject->foo = 'bar';

        $set = ResolvedFixtureSetFactory::create(
            null,
            (new FixtureBag())
                ->with(
                    $dummyFixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create()),
                )
                ->with(
                    $anotherDummyFixture = new SimpleFixture('another_dummy', 'Dummy', SpecificationBagFactory::create()),
                ),
            new ObjectBag(['dummy' => $expectedObject]),
        );
        $scope = ['injected' => true];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('bar');

        $resolver = new SelfFixtureReferenceResolver(new FakeChainableValueResolver());
        $actual = $resolver->resolve($value, $dummyFixture, $set, $scope, $context);

        $expected = new ResolvedValueWithFixtureSet($expectedObject, $set);
        self::assertEquals($expected, $actual);

        $valueProphecy->getValue()->shouldHaveBeenCalledTimes(1);
    }

    public function testReturnsResultOfTheDecoratedResolverIfReferenceDoesNotMatchSelf(): void
    {
        $valueProphecy = $this->prophesize(ValueInterface::class);
        $valueProphecy->getValue()->willReturn('a_random_fixture_id');
        /** @var ValueInterface $value */
        $value = $valueProphecy->reveal();

        $expectedObject = new stdClass();
        $expectedObject->foo = 'bar';

        $set = ResolvedFixtureSetFactory::create(
            null,
            $fixtureBag = (new FixtureBag())
                ->with(
                    $dummyFixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create()),
                )
                ->with(
                    $anotherDummyFixture = new SimpleFixture('another_dummy', 'Dummy', SpecificationBagFactory::create()),
                ),
            new ObjectBag(['dummy' => $expectedObject]),
        );
        $scope = ['injected' => true];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('bar');

        $decoratedResolverProphecy = $this->prophesize(ChainableValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve($value, $dummyFixture, $set, $scope, $context)
            ->willReturn(
                $expected = new ResolvedValueWithFixtureSet(
                    $resolvedFixture = new SimpleFixture('resolved_fixture', 'Dummy', SpecificationBagFactory::create()),
                    ResolvedFixtureSetFactory::create(null, $fixtureBag->with($resolvedFixture)),
                ),
            );
        /** @var ChainableValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new SelfFixtureReferenceResolver($decoratedResolver);
        $actual = $resolver->resolve($value, $dummyFixture, $set, $scope, $context);

        self::assertEquals($expected, $actual);

        $valueProphecy->getValue()->shouldHaveBeenCalledTimes(1);
        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
