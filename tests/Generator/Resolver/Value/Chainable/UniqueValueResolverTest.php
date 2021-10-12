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
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\UniqueValuesPool;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use ReflectionProperty;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\UniqueValueResolver
 */
class UniqueValueResolverTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ReflectionProperty
     */
    private $resolverRefl;

    /**
     * @var ReflectionProperty
     */
    private $poolRefl;
    
    protected function setUp(): void
    {
        $reflClass = new ReflectionClass(UniqueValueResolver::class);

        $this->resolverRefl = $reflClass->getProperty('resolver');
        $this->resolverRefl->setAccessible(true);

        $this->poolRefl = $reflClass->getProperty('pool');
        $this->poolRefl->setAccessible(true);
    }

    public function testIsAChainableResolver(): void
    {
        static::assertTrue(is_a(UniqueValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(UniqueValueResolver::class))->isCloneable());
    }

    public function testThrowsExceptionIfInvalidLimitGiven(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected limit value to be a strictly positive integer, got "0" instead.');

        new UniqueValueResolver(new UniqueValuesPool(), null, 0);
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $resolver = new UniqueValueResolver(new UniqueValuesPool());
        $newResolver = $resolver->withValueResolver(new FakeValueResolver());

        static::assertEquals(
            new UniqueValueResolver(new UniqueValuesPool()),
            $resolver
        );
        static::assertEquals(
            new UniqueValueResolver(new UniqueValuesPool(), new FakeValueResolver()),
            $newResolver
        );
    }

    public function testCanResolveDynamicArrayValues(): void
    {
        $resolver = new UniqueValueResolver(new UniqueValuesPool());

        static::assertTrue($resolver->canResolve(new UniqueValue('', '')));
        static::assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testCannotResolveValueIfHasNoResolver(): void
    {
        $resolver = new UniqueValueResolver(new UniqueValuesPool());

        $this->expectException(ResolverNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\UniqueValueResolver::resolve" to be called only if it has a resolver.');

        $resolver->resolve(new UniqueValue('', ''), new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testThrowsExceptionIfLimitReached(): void
    {
        $value = new UniqueValue('uniqid', '');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new UniqueValueResolver(new UniqueValuesPool(), new FakeValueResolver(), 1);

        $this->expectException(UniqueValueGenerationLimitReachedException::class);
        $this->expectExceptionMessage('Could not generate a unique value after 1 attempts for "uniqid".');

        $resolver->resolve($value, $fixture, $set, [], new GenerationContext(), 1);
    }

    public function testReturnsResultIfResultDoesNotAlreadyExists(): void
    {
        $value = new UniqueValue('uniqid', 10);
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new UniqueValueResolver(new UniqueValuesPool(), new FakeValueResolver());
        $result = $resolver->resolve($value, $fixture, $set, [], new GenerationContext());

        static::assertEquals(10, $result->getValue());
        static::assertEquals($set, $result->getSet());
    }

    public function testResolvesValueFirstIfNecessary(): void
    {
        $realValue = new FakeValue();
        $value = new UniqueValue('uniqid', $realValue);
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();
        $scope = ['scope' => 'epocs'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve($realValue, $fixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    10,
                    $newSet = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']))
                )
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new UniqueValueResolver(new UniqueValuesPool(), $decoratedResolver);
        $result = $resolver->resolve($value, $fixture, $set, $scope, $context);

        static::assertEquals(10, $result->getValue());
        static::assertEquals($newSet, $result->getSet());

        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testResolvesValueAsMuchAsNecessaryIfValueIsNotUnique(): void
    {
        $uniqueId = 'uniqid';
        $realValue = new FakeValue();
        $value = new UniqueValue($uniqueId, $realValue);
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();
        $scope = ['scope' => 'epocs'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $pool = new UniqueValuesPool();
        $pool->add(new UniqueValue($uniqueId, 10));
        $pool->add(new UniqueValue($uniqueId, 11));

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $setAfterResolution0 = ResolvedFixtureSetFactory::create(new ParameterBag(['iteration' => 0]));
        $setAfterResolution1 = ResolvedFixtureSetFactory::create(new ParameterBag(['iteration' => 1]));
        $setAfterResolution2 = ResolvedFixtureSetFactory::create(new ParameterBag(['iteration' => 2]));
        $decoratedResolverProphecy
            ->resolve($realValue, $fixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    10,
                    $setAfterResolution0
                )
            )
        ;
        $decoratedResolverProphecy
            ->resolve($realValue, $fixture, $setAfterResolution0, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    11,
                    $setAfterResolution1
                )
            )
        ;
        $decoratedResolverProphecy
            ->resolve($realValue, $fixture, $setAfterResolution1, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    12,
                    $setAfterResolution2
                )
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();


        $resolver = new UniqueValueResolver($pool, $decoratedResolver);
        $result = $resolver->resolve($value, $fixture, $set, $scope, $context);

        static::assertEquals(12, $result->getValue());
        static::assertEquals($setAfterResolution2, $result->getSet());

        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(3);
    }

    public function testThrowsIfLimitForGenerationOfUniqueValuesIsReached(): void
    {
        $uniqueId = 'uniqid';
        $realValue = new FakeValue();
        $value = new UniqueValue($uniqueId, $realValue);
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();
        $scope = ['scope' => 'epocs'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $pool = new UniqueValuesPool();
        $pool->add(new UniqueValue($uniqueId, 10));
        $pool->add(new UniqueValue($uniqueId, 11));

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve($realValue, $fixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(10, $set)
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();


        $resolver = new UniqueValueResolver($pool, $decoratedResolver);

        try {
            $resolver->resolve($value, $fixture, $set, $scope, $context);
            static::fail('Expected exception to be thrown.');
        } catch (UniqueValueGenerationLimitReachedException $exception) {
            $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(150);
        }
    }
}
