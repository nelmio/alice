<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\UniqueValuesPool;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Value\Chainable\DynamicArrayValueResolver
 */
class DynamicArrayValueResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $resolverRefl;

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

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $resolver = new DynamicArrayValueResolver();
        clone $resolver;
    }

    public function testImmutableFactories()
    {
        $decoratedResolver = new FakeValueResolver();
        $original = new DynamicArrayValueResolver();

        $clone = $original->withResolver($decoratedResolver);

        $this->assertInstanceOf(DynamicArrayValueResolver::class, $clone);
        $this->assertNull($this->resolverRefl->getValue($original));
        $this->assertSame($decoratedResolver, $this->resolverRefl->getValue($clone));
    }

    public function testCanResolveDynamicArrayValues()
    {
        $resolver = new DynamicArrayValueResolver();

        $this->assertTrue($resolver->canResolve(new DynamicArrayValue('', '')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\DynamicArrayValueResolver::resolve" to be called only if it has a resolver.
     */
    public function testCannotResolveValueIfHasNoResolver()
    {
        $value = new DynamicArrayValue('', '');
        $resolver = new DynamicArrayValueResolver();
        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create());
    }

    public function testResolvesQuantifierIfIsAValue()
    {
        $quantifier = new FakeValue();
        $value = new DynamicArrayValue($quantifier, '');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve($quantifier, $fixture, $set, [])
            ->willReturn(
                new ResolvedValueWithFixtureSet(10, ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar'])))
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new DynamicArrayValueResolver($decoratedResolver);
        $resolver->resolve($value, $fixture, $set);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected quantifier to be an integer superior or equal to 2. Got "1" for "Nelmio\Alice\Definition\Fixture\DummyFixture#dummy", check you dynamic arrays declarations (e.g. "<numberBetween(1, 2)>x @user*").
     */
    public function testThrowsExceptionIfInvalidQuantifier()
    {
        $quantifier = new FakeValue();
        $value = new DynamicArrayValue($quantifier, '');
        $fixture = new DummyFixture('dummy');
        $set = ResolvedFixtureSetFactory::create();

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve($quantifier, $fixture, $set, [])
            ->willReturn(
                new ResolvedValueWithFixtureSet(1, ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar'])))
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new DynamicArrayValueResolver($decoratedResolver);
        $resolver->resolve($value, $fixture, $set);

        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testDoesNotResolveElementIfIsNotAValue()
    {
        $quantifier = new FakeValue();
        $value = new DynamicArrayValue($quantifier, 'static val');
        $fixture = new DummyFixture('dummy');
        $set = ResolvedFixtureSetFactory::create();

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve($quantifier, $fixture, $set, [])
            ->willReturn(
                new ResolvedValueWithFixtureSet(2, $newSet = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar'])))
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new DynamicArrayValueResolver($decoratedResolver);
        $result = $resolver->resolve($value, $fixture, $set);

        $this->assertSame(['static val', 'static val'], $result->getValue());
        $this->assertEquals($newSet, $result->getSet());
    }

    public function testResolvesElementEachTimeIfIsAValue()
    {
        $quantifier = 2;
        $element = new FakeValue();
        $value = new DynamicArrayValue($quantifier, $element);
        $fixture = new DummyFixture('dummy');
        $set = ResolvedFixtureSetFactory::create();

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $setAfterFirstResolution = ResolvedFixtureSetFactory::create(new ParameterBag(['iteration' => 0]));
        $decoratedResolverProphecy
            ->resolve($element, $fixture, $set, [])
            ->willReturn(
                new ResolvedValueWithFixtureSet(10, $setAfterFirstResolution)
            )
        ;
        $setAfterSecondResolution = ResolvedFixtureSetFactory::create(new ParameterBag(['iteration' => 1]));
        $decoratedResolverProphecy
            ->resolve($element, $fixture, $setAfterFirstResolution, [])
            ->willReturn(
                new ResolvedValueWithFixtureSet(100, $setAfterSecondResolution)
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new DynamicArrayValueResolver($decoratedResolver);
        $result = $resolver->resolve($value, $fixture, $set);

        $this->assertSame([10, 100], $result->getValue());
        $this->assertEquals($setAfterSecondResolution, $result->getSet());
    }
}
