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
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Definition\Value\DummyValue;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Entity\StdClassFactory;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\FakeObjectGenerator;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\Throwable\Exception\Generator\ObjectGenerator\ObjectGeneratorNotFoundException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\FixtureNotFoundException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureReferenceResolver
 */
class FixtureReferenceResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAChainableResolver(): void
    {
        static::assertTrue(is_a(FixtureReferenceResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(FixtureReferenceResolver::class))->isCloneable());
    }

    public function testIsGeneratorAware(): void
    {
        $generator = new FakeObjectGenerator();

        $resolver = new FixtureReferenceResolver();
        $newResolver = $resolver->withObjectGenerator($generator);

        static::assertEquals(new FixtureReferenceResolver(), $resolver);
        static::assertEquals(new FixtureReferenceResolver($generator), $newResolver);
    }

    public function testCanResolveFixtureReferenceValues(): void
    {
        $resolver = new FixtureReferenceResolver();

        static::assertTrue($resolver->canResolve(new FixtureReferenceValue('')));
        static::assertFalse($resolver->canResolve(new FakeValue()));
    }

    public function testCannotResolveValueIfHasNoGenerator(): void
    {
        $resolver = new FixtureReferenceResolver();

        $this->expectException(ObjectGeneratorNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureReferenceResolver::resolve" to be called only if it has a generator.');

        $resolver->resolve(
            new FakeValue(),
            new FakeFixture(),
            ResolvedFixtureSetFactory::create(),
            [],
            new GenerationContext()
        );
    }

    public function testCannotResolveReferenceIsTheReferenceIsAValue(): void
    {
        $resolver = new FixtureReferenceResolver(new FakeObjectGenerator());

        $this->expectException(UnresolvableValueException::class);
        $this->expectExceptionMessage('Could not resolve value "@foo".');

        $resolver->resolve(
            new FixtureReferenceValue(new DummyValue('foo')),
            new FakeFixture(),
            ResolvedFixtureSetFactory::create(),
            [],
            new GenerationContext()
        );
    }

    public function testIfTheReferenceRefersToACompletelyGeneratedFixtureThenReturnsTheInstance(): void
    {
        $value = new FixtureReferenceValue('dummy');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create(
            null,
            null,
            new ObjectBag(['dummy' => $expectedInstance = new stdClass()])
        );
        $scope = [];
        $context = new GenerationContext();

        $expected = new ResolvedValueWithFixtureSet($expectedInstance, $set);

        $resolver = new FixtureReferenceResolver(new FakeObjectGenerator());
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        static::assertEquals($expected, $actual);
    }

    public function testIfTheReferenceRefersToAnInstantiatedFixtureAndRequiresToBeCompleteThenGenerateIt(): void
    {
        $value = new FixtureReferenceValue('dummy');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create(
            null,
            $fixtures = (new FixtureBag())->with(
                $referredFixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create())
            ),
            (new ObjectBag())->with(
                new SimpleObject(
                    'dummy',
                    $expectedInstance = new stdClass()
                )
            )
        );
        $scope = [];
        $context = new GenerationContext();
        $context->markAsNeedsCompleteGeneration();

        $generatorContext = new GenerationContext();
        $generatorContext->markIsResolvingFixture('dummy');
        $generatorContext->markAsNeedsCompleteGeneration();

        $generatorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
        $generatorProphecy
            ->generate($referredFixture, $set, $generatorContext)
            ->willReturn(
                $objects = new ObjectBag([
                    'dummy' => $expectedInstance = StdClassFactory::create([
                        'complete' => true,
                    ])
                ])
            )
        ;
        /** @var ObjectGeneratorInterface $generator */
        $generator = $generatorProphecy->reveal();

        $expected = new ResolvedValueWithFixtureSet(
            $expectedInstance,
            ResolvedFixtureSetFactory::create(
                null,
                $fixtures,
                $objects
            )
        );

        $resolver = new FixtureReferenceResolver($generator);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        static::assertEquals($expected, $actual);
        static::assertEquals($context, $generatorContext);

        $generatorProphecy->generate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testIfTheReferenceRefersToANonInstantiatedFixtureThenGenerateItBeforeReturningTheInstance(): void
    {
        $value = new FixtureReferenceValue('dummy');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create(
            null,
            $fixtures = (new FixtureBag())->with(
                $referredFixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create())
            ),
            null
        );
        $scope = [];
        $context = new GenerationContext();

        $generatorContext = new GenerationContext();
        $generatorContext->markIsResolvingFixture('dummy');
        $generatorContext->markAsNeedsCompleteGeneration();

        $generatorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
        $generatorProphecy
            ->generate($referredFixture, $set, $generatorContext)
            ->willReturn(
                $objects = new ObjectBag(['dummy' => $expectedInstance = new stdClass()])
            )
        ;
        /** @var ObjectGeneratorInterface $generator */
        $generator = $generatorProphecy->reveal();

        $expected = new ResolvedValueWithFixtureSet(
            $expectedInstance,
            ResolvedFixtureSetFactory::create(
                null,
                $fixtures,
                $objects
            )
        );

        $resolver = new FixtureReferenceResolver($generator);
        $actual = $resolver->resolve($value, $fixture, $set, $scope, $context);

        $generatorContext->unmarkAsNeedsCompleteGeneration();

        static::assertEquals($expected, $actual);
        static::assertEquals($generatorContext, $context);

        $generatorProphecy->generate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testIfTheReferenceRefersToANonExistentFixtureAndNoInstanceIsAvailableThenThrowsAnException(): void
    {
        $value = new FixtureReferenceValue('dummy');
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();
        $scope = [];
        $context = new GenerationContext();

        $resolver = new FixtureReferenceResolver(new FakeObjectGenerator());

        $this->expectException(FixtureNotFoundException::class);
        $this->expectExceptionMessage('Could not find the fixture "dummy".');

        $resolver->resolve($value, $fixture, $set, $scope, $context);
    }
}
