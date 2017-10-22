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
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\MutableValue;
use Nelmio\Alice\Entity\DummyWithGetter;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\NoSuchMethodException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureMethodCallReferenceResolver
 */
class FixtureMethodCallReferenceResolverTest extends TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(FixtureMethodCallReferenceResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(FixtureMethodCallReferenceResolver::class))->isCloneable());
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $resolver = new FixtureMethodCallReferenceResolver();
        $newResolver = $resolver->withValueResolver(new FakeValueResolver());

        $this->assertEquals(new FixtureMethodCallReferenceResolver(), $resolver);
        $this->assertEquals(new FixtureMethodCallReferenceResolver(new FakeValueResolver()), $newResolver);
    }

    public function testCanResolveMethodCallReferenceValues()
    {
        $resolver = new FixtureMethodCallReferenceResolver();

        $this->assertTrue($resolver->canResolve(new FixtureMethodCallValue(new FakeValue(), new FunctionCallValue('method'))));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureMethodCallReferenceResolver::resolve" to be called only if it has a resolver.
     */
    public function testCannotResolveValueIfHasNoResolver()
    {
        $value = new FixtureMethodCallValue(new FakeValue(), new FunctionCallValue('method'));
        $resolver = new FixtureMethodCallReferenceResolver();
        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testReturnsSetWithResolvedValue()
    {
        $value = new FixtureMethodCallValue(
            $reference = new FakeValue(),
            $functionCall = new FunctionCallValue('getFoo', [
                $arg1 = new FakeValue(),
            ])
        );
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']));
        $scope = ['val' => 'scopie'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $valueResolverContext = new GenerationContext();
        $valueResolverContext->markIsResolvingFixture('foo');
        $valueResolverContext->markAsNeedsCompleteGeneration();

        $dummyProphecy = $this->prophesize(DummyWithGetter::class);
        $dummyProphecy->getFoo('resolved_argument')
            ->shouldBeCalled()
            ->willReturn('resolved_value')
        ;

        /** @var DummyWithGetter $dummy */
        $dummy = $dummyProphecy->reveal();

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve($arg1, $fixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    'resolved_argument',
                    $newSet = ResolvedFixtureSetFactory::create(new ParameterBag(['ping' => 'pong']))
                )
            )
        ;
        $valueResolverProphecy
            ->resolve($reference, $fixture, $newSet, $scope, $valueResolverContext)
            ->willReturn(
                new ResolvedValueWithFixtureSet($dummy, $newSet)
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $expected = new ResolvedValueWithFixtureSet('resolved_value', $newSet);

        $resolver = new FixtureMethodCallReferenceResolver($valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        $this->assertEquals($expected, $actual);

        $valueResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
        $dummyProphecy->getFoo(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testThrowsAResolverExceptionOnError()
    {
        try {
            $value = new FixtureMethodCallValue(
                $reference = new MutableValue(new DummyWithGetter()),
                $functionCall = new FunctionCallValue('getFoo')
            );
            $set = ResolvedFixtureSetFactory::create(new ParameterBag(['foo' => 'bar']));

            $error = new \Error();
            $dummyProphecy = $this->prophesize(DummyWithGetter::class);
            $dummyProphecy->getFoo()->will(function () use ($error) {
                throw $error;
            });

            $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
            $valueResolverProphecy
                ->resolve($reference, Argument::cetera())
                ->willReturn(
                    new ResolvedValueWithFixtureSet(
                        $instance = $dummyProphecy->reveal(),
                        $newSet = ResolvedFixtureSetFactory::create(new ParameterBag())
                    )
                )
            ;
            /** @var ValueResolverInterface $valueResolver */
            $valueResolver = $valueResolverProphecy->reveal();

            $resolver = new FixtureMethodCallReferenceResolver($valueResolver);
            $resolver->resolve($value, new FakeFixture(), $set, [], new GenerationContext());

            $this->fail('Expected exception to be thrown.');
        } catch (UnresolvableValueException $exception) {
            $this->assertEquals(
                'Could not resolve value "mutable->getFoo()".',
                $exception->getMessage()
            );
            $this->assertEquals(0, $exception->getCode());
            $this->assertSame($error, $exception->getPrevious());
        }
    }

    public function testThrowsAnExceptionIfResolvedReferenceHasNoSuchMethod()
    {
        try {
            $instance = new DummyWithGetter();
            $value = new FixtureMethodCallValue(
                $reference = new MutableValue($instance),
                $functionCall = new FunctionCallValue('getNonExistent')
            );

            $set = ResolvedFixtureSetFactory::create();

            $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
            $valueResolverProphecy
                ->resolve(Argument::cetera())
                ->willReturn(
                    new ResolvedValueWithFixtureSet(new \stdClass(), $set)
                )
            ;
            /** @var ValueResolverInterface $valueResolver */
            $valueResolver = $valueResolverProphecy->reveal();

            $resolver = new FixtureMethodCallReferenceResolver($valueResolver);
            $resolver->resolve(
                $value,
                new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create()),
                $set,
                [],
                new GenerationContext()
            );

            $this->fail('Expected exception to be thrown.');
        } catch (NoSuchMethodException $exception) {
            $this->assertEquals(
                'Could not find the method "getNonExistent" of the object "dummy" (class: Dummy).',
                $exception->getMessage()
            );
            $this->assertEquals(0, $exception->getCode());
            $this->assertNull($exception->getPrevious());
        }
    }
}
