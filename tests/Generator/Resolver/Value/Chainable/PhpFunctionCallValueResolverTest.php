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

use PHPUnit\Framework\TestCase;
use Faker\Factory as FakerGeneratorFactory;
use Faker\Generator as FakerGenerator;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\PhpFunctionCallValueResolver
 */
class PhpFunctionCallValueResolverTest extends TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(PhpFunctionCallValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\UnclonableException
     */
    public function testIsNotClonable()
    {
        clone new PhpFunctionCallValueResolver([], new FakeValueResolver());
    }

    public function testCanResolvePropertyReferenceValues()
    {
        $resolver = new PhpFunctionCallValueResolver([], new FakeValueResolver());

        $this->assertTrue($resolver->canResolve(new FunctionCallValue('')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testReturnsSetWithEvaluatedValueIfFunctionIsAPhpNativeFunction()
    {
        $value = new FunctionCallValue('strtolower', ['BAR']);
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']));
        $scope = ['val' => 'scopie'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $expected = new ResolvedValueWithFixtureSet('bar', $set);

        $resolver = new PhpFunctionCallValueResolver([], new FakeValueResolver());
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        $this->assertEquals($expected, $actual);
    }

    public function testReturnsResultOfTheDecoratedResolverIfFunctionIsNotAPhpNativeFunction()
    {
        $value = new FunctionCallValue('foo');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']));
        $scope = ['val' => 'scopie'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(
                $value,
                $fixture,
                $set,
                $scope,
                $context
            )
            ->willReturn(
                $expected = new ResolvedValueWithFixtureSet(
                    'bar',
                    ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar', 'ping' => 'pong']))
                )
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new PhpFunctionCallValueResolver([], $decoratedResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        $this->assertEquals($expected, $actual);

        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testReturnsResultOfTheDecoratedResolverIfFunctionIsBlacklisted()
    {
        $value = new FunctionCallValue('strtolower', ['BAR']);
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']));
        $scope = ['val' => 'scopie'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(
                $value,
                $fixture,
                $set,
                $scope,
                $context
            )
            ->willReturn(
                $expected = new ResolvedValueWithFixtureSet(
                    'bar',
                    ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar', 'ping' => 'pong']))
                )
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new PhpFunctionCallValueResolver(['strtolower'], $decoratedResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        $this->assertEquals($expected, $actual);

        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
