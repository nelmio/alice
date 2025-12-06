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
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FixtureMatchReferenceValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
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
#[\PHPUnit\Framework\Attributes\CoversClass(FixtureWildcardReferenceResolver::class)]
final class FixtureWildcardReferenceResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableResolver(): void
    {
        self::assertTrue(is_a(FixtureWildcardReferenceResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(FixtureWildcardReferenceResolver::class))->isCloneable());
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $resolver = new FixtureWildcardReferenceResolver();
        $newResolver = $resolver->withValueResolver(new FakeValueResolver());

        self::assertEquals(new FixtureWildcardReferenceResolver(), $resolver);
        self::assertEquals(new FixtureWildcardReferenceResolver(new FakeValueResolver()), $newResolver);
    }

    public function testCanResolveFixtureMatchReferenceValues(): void
    {
        $resolver = new FixtureWildcardReferenceResolver();

        self::assertTrue($resolver->canResolve(new FixtureMatchReferenceValue('')));
        self::assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testCannotResolveValueIfHasNoResolver(): void
    {
        $resolver = new FixtureWildcardReferenceResolver();

        $this->expectException(ResolverNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureWildcardReferenceResolver::resolve" to be called only if it has a resolver.');

        $resolver->resolve(new FakeValue(), new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testReturnsARandomMatchingFixture(): void
    {
        $value = FixtureMatchReferenceValue::createWildcardReference('dummy');
        $set = ResolvedFixtureSetFactory::create(
            $parameters = new ParameterBag(['foo' => 'bar']),
            $fixtures = (new FixtureBag())
                ->with($fixture = new DummyFixture('dummy')),
        );
        $scope = ['val' => 'scopie'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve(new FixtureReferenceValue('dummy'), $fixture, $set, $scope, $context)
            ->willReturn(
                $expected = new ResolvedValueWithFixtureSet(
                    'dummy',
                    $newSet = ResolvedFixtureSetFactory::create(
                        $parameters = new ParameterBag(['ping' => 'pong']),
                        $fixtures = (new FixtureBag())->with($fixture),
                    ),
                ),
            );
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $resolver = new FixtureWildcardReferenceResolver($valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        self::assertSame($expected, $actual);

        $valueResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testTheMatchingFixtureCanBeFromLoadedFixtures(): void
    {
        $value = FixtureMatchReferenceValue::createWildcardReference('dummy');
        $fixture = new DummyFixture('injected_fixture');
        $set = ResolvedFixtureSetFactory::create(
            null,
            (new FixtureBag())
                ->with(new DummyFixture('dummy')),
        );
        $scope = ['foo' => 'bar'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve(new FixtureReferenceValue('dummy'), $fixture, $set, $scope, $context)
            ->willReturn(
                $expected = new ResolvedValueWithFixtureSet(
                    'dummy',
                    $newSet = ResolvedFixtureSetFactory::create(
                        new ParameterBag(['ping' => 'pong']),
                        (new FixtureBag())->with($fixture),
                    ),
                ),
            );
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $resolver = new FixtureWildcardReferenceResolver($valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        self::assertSame($expected, $actual);
    }

    public function testTheMatchingFixtureCanBeFromObjects(): void
    {
        $value = FixtureMatchReferenceValue::createWildcardReference('dummy');
        $fixture = new DummyFixture('injected_fixture');
        $set = ResolvedFixtureSetFactory::create(
            null,
            null,
            (new ObjectBag())
                ->with(new SimpleObject('dummy', new stdClass())),
        );
        $scope = [];
        $context = new GenerationContext();

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve(new FixtureReferenceValue('dummy'), $fixture, $set, $scope, $context)
            ->willReturn(
                $expected = new ResolvedValueWithFixtureSet(
                    'dummy',
                    $newSet = ResolvedFixtureSetFactory::create(
                        new ParameterBag(['ping' => 'pong']),
                        (new FixtureBag())->with($fixture),
                    ),
                ),
            );
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $resolver = new FixtureWildcardReferenceResolver($valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        self::assertSame($expected, $actual);
    }

    public function testThrowsAnExceptionIfNotMathcingIdFound(): void
    {
        $value = FixtureMatchReferenceValue::createWildcardReference('dummy');
        $fixture = new DummyFixture('injected_fixture');
        $set = ResolvedFixtureSetFactory::create();
        $scope = [];
        $context = new GenerationContext();

        $resolver = new FixtureWildcardReferenceResolver(new FakeValueResolver());

        $this->expectException(UnresolvableValueException::class);
        $this->expectExceptionMessage('Could not find a fixture or object ID matching the pattern');

        $resolver->resolve($value, $fixture, $set, $scope, $context);
    }
}
