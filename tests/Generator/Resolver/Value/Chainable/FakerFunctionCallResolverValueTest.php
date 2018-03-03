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
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\ResolvedFunctionCallValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\FakerFunctionCallValueResolver
 */
class FakerFunctionCallValueResolverValueTest extends TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(FakerFunctionCallValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(FakerFunctionCallValueResolver::class))->isCloneable());
    }

    public function testCanResolvePropertyReferenceValues()
    {
        $resolver = new FakerFunctionCallValueResolver(FakerGeneratorFactory::create());

        $this->assertTrue($resolver->canResolve(new ResolvedFunctionCallValue('')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testReturnsSetWithResolvedValue()
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

        $this->assertEquals($expected, $actual);
    }

    public function testCallAProviderFunction()
    {
        $value = new ResolvedFunctionCallValue('lexify', ['Hello ???']);
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new FakerFunctionCallValueResolver(FakerGeneratorFactory::create(), new FakeValueResolver());
        $result = $resolver->resolve($value, $fixture, $set, [], new GenerationContext());

        $this->assertEquals(9, strlen($result->getValue()));
        $this->assertEquals('Hello ', substr($result->getValue(), 0, 6));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown formatter "unknown"
     */
    public function testThrowsAnExceptionIfTriesToCallAnUndefinedProviderFunction()
    {
        $value = new ResolvedFunctionCallValue('unknown');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new FakerFunctionCallValueResolver(FakerGeneratorFactory::create(), new FakeValueResolver());
        $resolver->resolve($value, $fixture, $set, [], new GenerationContext());
    }
}
