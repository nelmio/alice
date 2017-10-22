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
use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Definition\Value\OptionalValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use phpmock\functions\FixedValueFunction;
use phpmock\MockBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\OptionalValueResolver
 */
class OptionalValueResolverTest extends TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(OptionalValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(OptionalValueResolver::class))->isCloneable());
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $resolver = new OptionalValueResolver();
        $newResolver = $resolver->withValueResolver(new FakeValueResolver());

        $this->assertEquals(new OptionalValueResolver(), $resolver);
        $this->assertEquals(new OptionalValueResolver(new FakeValueResolver(), new FakeValueResolver()), $newResolver);
    }

    public function testCanResolveOptionalValues()
    {
        $resolver = new OptionalValueResolver();

        $this->assertTrue($resolver->canResolve(new OptionalValue('', '')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\OptionalValueResolver::resolve" to be called only if it has a resolver.
     */
    public function testCannotResolveValueIfHasNoResolver()
    {
        $value = new FixturePropertyValue(new FakeValue(), '');
        $resolver = new OptionalValueResolver();
        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testCanHandleExtremaQuantifiersCorrectly()
    {
        $resolver = new OptionalValueResolver(new FakeValueResolver());

        $builder = new MockBuilder();
        $builder->setNamespace(__NAMESPACE__)
            ->setName('mt_rand');

        $builder->setFunctionProvider(new FixedValueFunction(0));
        $mock = $builder->build();
        $mock->enable();

        $value = new OptionalValue(0, 'first_0', 'second_0');
        $resolvedValueFor0 = $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());

        $mock->disable();

        $builder->setFunctionProvider(new FixedValueFunction(99));
        $mock = $builder->build();
        $mock->enable();

        $value = new OptionalValue(100, 'first_100', 'second_100');
        $resolvedValueFor100 = $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());

        $mock->disable();

        $builder->setFunctionProvider(new FixedValueFunction(49));
        $mock = $builder->build();
        $mock->enable();

        $value = new OptionalValue(50, 'first_50', 'second_50');
        $resolvedValueFor50 = $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());

        $mock->disable();

        $this->assertEquals('second_0', $resolvedValueFor0->getValue());
        $this->assertEquals('first_100', $resolvedValueFor100->getValue());
        $this->assertEquals('first_50', $resolvedValueFor50->getValue());
    }

    public function testReturnsSetWithResolvedValue()
    {
        //TODO
    }
}
