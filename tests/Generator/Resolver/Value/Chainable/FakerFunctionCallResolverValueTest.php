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

use Faker\Factory as FakerGeneratorFactory;
use Faker\Generator as FakerGenerator;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ParameterBag;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\FakerFunctionCallValueResolver
 */
class FakerFunctionCallValueResolverValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(FakerFunctionCallValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new FakerFunctionCallValueResolver(FakerGeneratorFactory::create());
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $resolver = new FakerFunctionCallValueResolver(FakerGeneratorFactory::create());
        $newResolver = $resolver->withValueResolver(new FakeValueResolver());

        $this->assertEquals(new FakerFunctionCallValueResolver(FakerGeneratorFactory::create()), $resolver);
        $this->assertEquals(new FakerFunctionCallValueResolver(FakerGeneratorFactory::create(), new FakeValueResolver()), $newResolver);
    }

    public function testCanResolvePropertyReferenceValues()
    {
        $resolver = new FakerFunctionCallValueResolver(FakerGeneratorFactory::create());

        $this->assertTrue($resolver->canResolve(new FunctionCallValue('')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\FakerFunctionCallValueResolver::resolve" to be called only if it has a resolver.
     */
    public function testCannotResolveValueIfHasNoResolver()
    {
        $value = new FixturePropertyValue(new FakeValue(), '');
        $resolver = new FakerFunctionCallValueResolver(FakerGeneratorFactory::create());
        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testReturnsSetWithResolvedValue()
    {
        $value = new FunctionCallValue('foo');
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

        $resolver = new FakerFunctionCallValueResolver($fakerGenerator, new FakeValueResolver());
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        $this->assertEquals($expected, $actual);
    }

    public function testResolvesAllArgumentsValuesBeforePassingThemToTheGenerator()
    {
        $value = new FunctionCallValue(
            'foo',
            [
                'scalar',
                new FakeValue(),
                'another scalar',
            ]
        );
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']));
        $scope = ['val' => 'scopie'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve(new FakeValue(), $fixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    $instance = new \stdClass(),
                    $newSet = ResolvedFixtureSetFactory::create(new ParameterBag(['ping' => 'pong']))
                )
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $fakerGeneratorProphecy = $this->prophesize(FakerGenerator::class);
        $fakerGeneratorProphecy
            ->format(
                'foo',
                [
                    'scalar',
                    $instance,
                    'another scalar',
                ])
            ->willReturn('bar')
        ;
        /** @var FakerGenerator $fakerGenerator */
        $fakerGenerator = $fakerGeneratorProphecy->reveal();

        $expected = new ResolvedValueWithFixtureSet('bar', $newSet);

        $resolver = new FakerFunctionCallValueResolver($fakerGenerator, $valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        $this->assertEquals($expected, $actual);
    }

    public function testCallAProviderFunction()
    {
        $value = new FunctionCallValue('lexify', ['Hello ???']);
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
        $value = new FunctionCallValue('unknown');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();

        $resolver = new FakerFunctionCallValueResolver(FakerGeneratorFactory::create(), new FakeValueResolver());
        $resolver->resolve($value, $fixture, $set, [], new GenerationContext());
    }
}
