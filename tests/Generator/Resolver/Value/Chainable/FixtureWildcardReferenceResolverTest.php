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
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureWildcardReferenceResolver
 */
class FixtureWildcardReferenceResolverTest extends TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(FixtureWildcardReferenceResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(FixtureWildcardReferenceResolver::class))->isCloneable());
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $resolver = new FixtureWildcardReferenceResolver();
        $newResolver = $resolver->withValueResolver(new FakeValueResolver());

        $this->assertEquals(new FixtureWildcardReferenceResolver(), $resolver);
        $this->assertEquals(new FixtureWildcardReferenceResolver(new FakeValueResolver()), $newResolver);
    }

    public function testCanResolveFixtureMatchReferenceValues()
    {
        $resolver = new FixtureWildcardReferenceResolver();

        $this->assertTrue($resolver->canResolve(new FixtureMatchReferenceValue('')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureWildcardReferenceResolver::resolve" to be called only if it has a resolver.
     */
    public function testCannotResolveValueIfHasNoResolver()
    {
        $resolver = new FixtureWildcardReferenceResolver();
        $resolver->resolve(new FakeValue(), new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testReturnsARandomMatchingFixture()
    {
        $value = FixtureMatchReferenceValue::createWildcardReference('dummy');
        $set = ResolvedFixtureSetFactory::create(
            $parameters = new ParameterBag(['foo' => 'bar']),
            $fixtures = (new FixtureBag())
                ->with($fixture = new DummyFixture('dummy'))
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
                        $fixtures = (new FixtureBag())->with($fixture)
                    )
                )
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $resolver = new FixtureWildcardReferenceResolver($valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        $this->assertSame($expected, $actual);

        $valueResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testTheMatchingFixtureCanBeFromLoadedFixtures()
    {
        $value = FixtureMatchReferenceValue::createWildcardReference('dummy');
        $fixture = new DummyFixture('injected_fixture');
        $set = ResolvedFixtureSetFactory::create(
            null,
            (new FixtureBag())
                ->with(new DummyFixture('dummy'))
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
                        (new FixtureBag())->with($fixture)
                    )
                )
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $resolver = new FixtureWildcardReferenceResolver($valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        $this->assertSame($expected, $actual);
    }

    public function testTheMatchingFixtureCanBeFromObjects()
    {
        $value = FixtureMatchReferenceValue::createWildcardReference('dummy');
        $fixture = new DummyFixture('injected_fixture');
        $set = ResolvedFixtureSetFactory::create(
            null,
            null,
            (new ObjectBag())
                ->with(new SimpleObject('dummy', new \stdClass()))
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
                        (new FixtureBag())->with($fixture)
                    )
                )
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $resolver = new FixtureWildcardReferenceResolver($valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException
     * @expectedExceptionMessage Could not find a fixture or object ID matching the pattern
     */
    public function testThrowsAnExceptionIfNotMathcingIdFound()
    {
        $value = FixtureMatchReferenceValue::createWildcardReference('dummy');
        $fixture = new DummyFixture('injected_fixture');
        $set = ResolvedFixtureSetFactory::create();
        $scope = [];
        $context = new GenerationContext();

        $resolver = new FixtureWildcardReferenceResolver(new FakeValueResolver());
        $resolver->resolve($value, $fixture, $set, $scope, $context);
    }
}
