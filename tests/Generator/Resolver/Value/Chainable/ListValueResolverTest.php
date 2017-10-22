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

use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\ListValue;
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
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\ListValueResolver
 */
class ListValueResolverTest extends TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(ListValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ListValueResolver::class))->isCloneable());
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $resolver = new ListValueResolver();
        $newResolver = $resolver->withValueResolver(new FakeValueResolver());

        $this->assertEquals(new ListValueResolver(), $resolver);
        $this->assertEquals(new ListValueResolver(new FakeValueResolver(), new FakeValueResolver()), $newResolver);
    }

    public function testCanResolveOptionalValues()
    {
        $resolver = new ListValueResolver();

        $this->assertTrue($resolver->canResolve(new ListValue([])));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\ListValueResolver::resolve" to be called only if it has a resolver.
     */
    public function testCannotResolveValueIfHasNoResolver()
    {
        $value = new ListValue([]);
        $resolver = new ListValueResolver();
        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testImplodesTheGivenArrayOfValues()
    {
        $value = new ListValue(['a', 'b', 'c']);
        $expected = new ResolvedValueWithFixtureSet('abc', ResolvedFixtureSetFactory::create());

        $resolver = new ListValueResolver(new FakeValueResolver());
        $actual = $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());

        $this->assertEquals($expected, $actual);
    }

    public function testResolvesAllTheValuesInArrayBeforeImplodingIt()
    {
        $value = new ListValue(['a', new FakeValue(), 'c', new FakeValue()]);
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']));
        $scope = ['scope' => 'epocs'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve(new FakeValue(), $fixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    'b',
                    $newSet = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'baz']))
                )
            )
        ;
        $valueResolverProphecy
            ->resolve(new FakeValue(), $fixture, $newSet, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    'd',
                    $newSet2 = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'zab']))
                )
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $expected = new ResolvedValueWithFixtureSet('abcd', $newSet2);

        $resolver = new ListValueResolver($valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        $this->assertEquals($expected, $actual);

        $valueResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }
}
