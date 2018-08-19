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
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\DynamicArrayValueResolver
 */
class DynamicArrayValueResolverTest extends TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $resolverRefl;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $reflClass = new \ReflectionClass(DynamicArrayValueResolver::class);

        $this->resolverRefl = $reflClass->getProperty('resolver');
        $this->resolverRefl->setAccessible(true);
    }

    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(DynamicArrayValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(DynamicArrayValueResolver::class))->isCloneable());
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $resolver = new DynamicArrayValueResolver();
        $newResolver = $resolver->withValueResolver(new FakeValueResolver());

        $this->assertEquals(new DynamicArrayValueResolver(), $resolver);
        $this->assertEquals(new DynamicArrayValueResolver(new FakeValueResolver()), $newResolver);
    }

    public function testCanResolveDynamicArrayValues()
    {
        $resolver = new DynamicArrayValueResolver();

        $this->assertTrue($resolver->canResolve(new DynamicArrayValue(1, '')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\DynamicArrayValueResolver::resolve" to be called only if it has a resolver.
     */
    public function testCannotResolveValueIfHasNoResolver()
    {
        $value = new DynamicArrayValue(1, '');
        $resolver = new DynamicArrayValueResolver();
        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testIfQuantifierIsAValueThenItWillBeResolvedAsWell()
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected quantifier to be a positive integer. Got "-1" for "dummy", check you dynamic arrays declarations (e.g. "<numberBetween(1, 2)>x @user*").
     */
    public function testThrowsExceptionIfAnInvalidQuantifierIsGiven()
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
        $resolver->resolve($value, $fixture, $set, $scope, $context);

        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testDoesNotResolveElementIfIsNotAValue()
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

        $this->assertSame(['static val', 'static val'], $result->getValue());
        $this->assertEquals($newSet, $result->getSet());
    }

    public function testResolvesElementAsManyTimeAsNecessaryIfItIsAValue()
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

        $this->assertSame([10, 100], $result->getValue());
        $this->assertEquals($setAfterSecondResolution, $result->getSet());
    }
}
