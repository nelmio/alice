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

use Faker\Generator;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Definition\Value\OptionalValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException;
use phpmock\functions\FixedValueFunction;
use phpmock\MockBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use function in_array;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\OptionalValueResolver
 */
class OptionalValueResolverTest extends TestCase
{
    use ProphecyTrait;

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
        $this->assertEquals(new OptionalValueResolver(new FakeValueResolver()), $newResolver);
    }

    public function testCanResolveOptionalValues()
    {
        $resolver = new OptionalValueResolver();

        $this->assertTrue($resolver->canResolve(new OptionalValue('', '')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testCannotResolveValueIfHasNoResolver()
    {
        $value = new FixturePropertyValue(new FakeValue(), '');
        $resolver = new OptionalValueResolver();

        $this->expectException(ResolverNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\OptionalValueResolver::resolve" to be called only if it has a resolver.');

        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    /**
     * @dataProvider optionalValueProvider
     */
    public function testCanHandleExtremaQuantifiersCorrectly(
        OptionalValue $value,
        int $randomValue,
        string $expectedValue
    ) {
        $generatorProphecy = $this->prophesize(Generator::class);
        $generatorProphecy->numberBetween(0, 99)->willReturn($randomValue);
        $generator = $generatorProphecy->reveal();

        $resolver = new OptionalValueResolver(new FakeValueResolver(), $generator);

        $resolvedValue = $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());

        $this->assertSame($expectedValue, $resolvedValue->getValue());
    }

    public function testCanHandleExtremaQuantifiersCorrectlyWithoutGenerator()
    {
        $resolver = new OptionalValueResolver(new FakeValueResolver());

        $value = new OptionalValue(0, 'first_0', 'second_0');

        $resolvedValue = $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());

        $this->assertTrue(in_array($resolvedValue->getValue(), ['first_0', 'second_0'], true));
    }

    public static function optionalValueProvider(): iterable
    {
        yield 'min' => [
            new OptionalValue(0, 'first_0', 'second_0'),
            0,
            'second_0',
        ];

        yield 'max' => [
            new OptionalValue(100, 'first_100', 'second_100'),
            99,
            'first_100',
        ];

        yield 'mid' => [
            new OptionalValue(50, 'first_50', 'second_50'),
            49,
            'first_50',
        ];
    }
}
