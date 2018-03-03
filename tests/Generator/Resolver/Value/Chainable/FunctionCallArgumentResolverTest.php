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
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\ResolvedFunctionCallValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\FunctionCallArgumentResolver
 */
class FunctionCallArgumentResolverTest extends TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(FunctionCallArgumentResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(FunctionCallArgumentResolver::class))->isCloneable());
    }

    public function testCanResolvePropertyReferenceValues()
    {
        $resolver = new FunctionCallArgumentResolver(new FakeValueResolver());

        $this->assertTrue($resolver->canResolve(new FunctionCallValue('')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testIsResolverAware()
    {
        $decoratedResolverConstructor = function () {
            $resolver = new FakeValueResolver();
            $resolver->decorated = true;

            return $resolver;
        };
        $argumentResolverConstructor = function () {
            $resolver = new FakeValueResolver();
            $resolver->argument = true;

            return $resolver;
        };

        $resolver = new FunctionCallArgumentResolver($decoratedResolverConstructor());
        $newResolver = $resolver->withValueResolver($argumentResolverConstructor());

        $this->assertNotSame($resolver, $newResolver);
        $this->assertEquals(
            new FunctionCallArgumentResolver($decoratedResolverConstructor()),
            $resolver
        );
        $this->assertEquals(
            new FunctionCallArgumentResolver($decoratedResolverConstructor(), $argumentResolverConstructor()),
            $newResolver
        );
    }

    public function testThrowsAnExceptionIfDoesNotHaveAnArgumentResolver()
    {
        $value = new FunctionCallValue('foo');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();
        $scope = ['val' => 'scopie'];
        $context = new GenerationContext();

        $resolver = new FunctionCallArgumentResolver(new FakeValueResolver());

        try {
            $resolver->resolve($value, $fixture, $set, $scope, $context);
            $this->fail('Expected exception to be thrown.');
        } catch (ResolverNotFoundException $exception) {
            $this->assertEquals(
                'Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\FunctionCallArgumentResolver::resolve"'
                .' to be called only if it has a resolver.',
                $exception->getMessage()
            );
            $this->assertEquals(0, $exception->getCode());
            $this->assertNull($exception->getPrevious());
        }
    }

    public function testResolvesAllArgumentsValuesBeforePassingThemToTheDecoratedResolver()
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

        $argumentResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $argumentResolverProphecy
            ->resolve(new FakeValue(), $fixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    $instance = new \stdClass(),
                    $newSet = ResolvedFixtureSetFactory::create(new ParameterBag(['ping' => 'pong']))
                )
            )
        ;
        /** @var ValueResolverInterface $argumentResolver */
        $argumentResolver = $argumentResolverProphecy->reveal();

        $decoratedResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(
                new ResolvedFunctionCallValue(
                    'foo',
                    [
                        'scalar',
                        $instance,
                        'another scalar',
                    ]
                ),
                $fixture,
                $newSet,
                $scope,
                $context
            )
            ->willReturn(
                $expected = new ResolvedValueWithFixtureSet(
                    'end',
                    ResolvedFixtureSetFactory::create(new ParameterBag(['gnip' => 'gnop']))
                )
            )
        ;
        /** @var ValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new FunctionCallArgumentResolver($decoratedResolver, $argumentResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        $this->assertEquals($expected, $actual);
    }
}
