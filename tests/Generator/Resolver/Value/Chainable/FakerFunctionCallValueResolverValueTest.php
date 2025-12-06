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

use Faker\Factory as FakerGeneratorFactory;
use Faker\Generator as FakerGenerator;
use InvalidArgumentException;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\ResolvedFunctionCallValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

/**
 * @internal
 */
#[CoversClass(FakerFunctionCallValueResolver::class)]
final class FakerFunctionCallValueResolverValueTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableResolver(): void
    {
        self::assertTrue(is_a(FakerFunctionCallValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(FakerFunctionCallValueResolver::class))->isCloneable());
    }

    public function testCanResolvePropertyReferenceValues(): void
    {
        $resolver = new FakerFunctionCallValueResolver(FakerGeneratorFactory::create());

        self::assertTrue($resolver->canResolve(new ResolvedFunctionCallValue('')));
        self::assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testReturnsSetWithResolvedValue(): void
    {
        $value = new ResolvedFunctionCallValue('foo');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']));
        $scope = ['val' => 'scopie'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $fakerGeneratorProphecy = $this->prophesize(FakerGenerator::class);
        $fakerGeneratorProphecy->format('foo', [])->willReturn('bar');
        /** @var FakerGenerator $fakerGenerator */
        $fakerGenerator = $fakerGeneratorProphecy->reveal();

        $expected = new ResolvedValueWithFixtureSet('bar', $set);

        $resolver = new FakerFunctionCallValueResolver($fakerGenerator);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        self::assertEquals($expected, $actual);
    }

    public function testCallAProviderFunction(): void
    {
        $value = new ResolvedFunctionCallValue('lexify', ['Hello ???']);
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new FakerFunctionCallValueResolver(FakerGeneratorFactory::create());
        $result = $resolver->resolve($value, $fixture, $set, [], new GenerationContext());

        self::assertEquals(9, mb_strlen($result->getValue()));
        self::assertEquals('Hello ', mb_substr($result->getValue(), 0, 6));
    }

    public function testThrowsAnExceptionIfTriesToCallAnUndefinedProviderFunction(): void
    {
        $value = new ResolvedFunctionCallValue('unknown');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new FakerFunctionCallValueResolver(FakerGeneratorFactory::create());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown format "unknown"');

        $resolver->resolve($value, $fixture, $set, [], new GenerationContext());
    }
}
