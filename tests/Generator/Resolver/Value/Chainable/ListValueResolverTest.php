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
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\ListValueResolver
 * @internal
 */
class ListValueResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableResolver(): void
    {
        self::assertTrue(is_a(ListValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(ListValueResolver::class))->isCloneable());
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $resolver = new ListValueResolver();
        $newResolver = $resolver->withValueResolver(new FakeValueResolver());

        self::assertEquals(new ListValueResolver(), $resolver);
        self::assertEquals(new ListValueResolver(new FakeValueResolver()), $newResolver);
    }

    public function testCanResolveOptionalValues(): void
    {
        $resolver = new ListValueResolver();

        self::assertTrue($resolver->canResolve(new ListValue([])));
        self::assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testCannotResolveValueIfHasNoResolver(): void
    {
        $value = new ListValue([]);
        $resolver = new ListValueResolver();

        $this->expectException(ResolverNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\ListValueResolver::resolve" to be called only if it has a resolver.');

        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testImplodesTheGivenArrayOfValues(): void
    {
        $value = new ListValue(['a', 'b', 'c']);
        $expected = new ResolvedValueWithFixtureSet('abc', ResolvedFixtureSetFactory::create());

        $resolver = new ListValueResolver(new FakeValueResolver());
        $actual = $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());

        self::assertEquals($expected, $actual);
    }

    public function testResolvesAllTheValuesInArrayBeforeImplodingIt(): void
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
                    $newSet = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'baz'])),
                ),
            );
        $valueResolverProphecy
            ->resolve(new FakeValue(), $fixture, $newSet, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    'd',
                    $newSet2 = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'zab'])),
                ),
            );
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $expected = new ResolvedValueWithFixtureSet('abcd', $newSet2);

        $resolver = new ListValueResolver($valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        self::assertEquals($expected, $actual);

        $valueResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }
}
