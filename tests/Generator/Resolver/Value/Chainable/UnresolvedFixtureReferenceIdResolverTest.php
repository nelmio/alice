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
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\FakeObjectGenerator;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeChainableValueResolver;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\UnresolvedFixtureReferenceIdResolver
 */
class UnresolvedFixtureReferenceIdResolverTest extends TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(UnresolvedFixtureReferenceIdResolver::class, ChainableValueResolverInterface::class, true));
    }

    public function testIsObjectGeneratorAware()
    {
        $this->assertTrue(is_a(UnresolvedFixtureReferenceIdResolver::class, ObjectGeneratorAwareInterface::class, true));
    }

    public function testIsValueResolverAware()
    {
        $this->assertTrue(is_a(UnresolvedFixtureReferenceIdResolver::class, ValueResolverAwareInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(UnresolvedFixtureReferenceIdResolver::class))->isCloneable());
    }

    public function testCanResolveTheValueResolvableByItsDecoratedResolver()
    {
        $value = new FakeValue();

        $decoratedResolverProphecy = $this->prophesize(ChainableValueResolverInterface::class);
        $decoratedResolverProphecy->canResolve($value)->willReturn(true);
        /** @var ChainableValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new UnresolvedFixtureReferenceIdResolver($decoratedResolver);

        $this->assertTrue($resolver->canResolve($value));

        $decoratedResolverProphecy->canResolve(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\UnresolvedFixtureReferenceIdResolver::resolve" to be called only if it has a resolver.
     */
    public function testCannotResolveValueIfHasNoResolver()
    {
        $value = new FakeValue();
        $resolver = new UnresolvedFixtureReferenceIdResolver(new FakeChainableValueResolver());
        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testPassesTheObjectGeneratorAwarenessPropertyToItsDecoratedResolver()
    {
        $generator = new FakeObjectGenerator();

        $resolver = new UnresolvedFixtureReferenceIdResolver(new FakeChainableValueResolver());
        $newResolver = $resolver->withObjectGenerator($generator);

        $this->assertEquals($newResolver, $resolver);
        $this->assertNotSame($newResolver, $resolver);


        $resolver = new UnresolvedFixtureReferenceIdResolver(new FakeChainableValueResolver(), new FakeValueResolver());
        $newResolver = $resolver->withObjectGenerator($generator);

        $this->assertEquals($newResolver, $resolver);
        $this->assertNotSame($newResolver, $resolver);


        $decoratedResolverProphecy = $this->prophesize(ChainableValueResolverInterface::class);
        $decoratedResolverProphecy->willImplement(ObjectGeneratorAwareInterface::class);
        $decoratedResolverProphecy
            ->withObjectGenerator($generator)
            ->willReturn($newDecoratedResolver = new FakeChainableValueResolver())
        ;
        /** @var ChainableValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new UnresolvedFixtureReferenceIdResolver($decoratedResolver);
        $newResolver = $resolver->withObjectGenerator($generator);

        $this->assertEquals(new UnresolvedFixtureReferenceIdResolver($decoratedResolver), $resolver);
        $this->assertEquals(new UnresolvedFixtureReferenceIdResolver($newDecoratedResolver), $newResolver);


        $resolver = new UnresolvedFixtureReferenceIdResolver($decoratedResolver, new FakeValueResolver());
        $newResolver = $resolver->withObjectGenerator($generator);

        $this->assertEquals(
            new UnresolvedFixtureReferenceIdResolver($decoratedResolver, new FakeValueResolver()),
            $resolver
        );
        $this->assertEquals(
            new UnresolvedFixtureReferenceIdResolver($newDecoratedResolver, new FakeValueResolver()),
            $newResolver
        );

        $decoratedResolverProphecy->withObjectGenerator(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function testPassesTheValeResolverAwarenessPropertyToItsDecoratedResolver()
    {
        $valueResolver = new FakeValueResolver();
        $injectedValueResolver = new FakeValueResolver();
        $injectedValueResolver->foo = 'bar';

        $resolver = new UnresolvedFixtureReferenceIdResolver(new FakeChainableValueResolver());
        $newResolver = $resolver->withValueResolver($valueResolver);

        $this->assertEquals(
            new UnresolvedFixtureReferenceIdResolver(new FakeChainableValueResolver()),
            $resolver
        );
        $this->assertEquals(
            new UnresolvedFixtureReferenceIdResolver(new FakeChainableValueResolver(), $valueResolver),
            $newResolver
        );


        $resolver = new UnresolvedFixtureReferenceIdResolver(new FakeChainableValueResolver(), $injectedValueResolver);
        $newResolver = $resolver->withValueResolver($valueResolver);

        $this->assertEquals(
            new UnresolvedFixtureReferenceIdResolver(new FakeChainableValueResolver(), $injectedValueResolver),
            $resolver
        );
        $this->assertEquals(
            new UnresolvedFixtureReferenceIdResolver(new FakeChainableValueResolver(), $valueResolver),
            $newResolver
        );


        $decoratedResolverProphecy = $this->prophesize(ChainableValueResolverInterface::class);
        $decoratedResolverProphecy->willImplement(ValueResolverAwareInterface::class);
        $decoratedResolverProphecy
            ->withValueResolver($valueResolver)
            ->willReturn($newDecoratedResolver = new FakeChainableValueResolver())
        ;
        /** @var ChainableValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new UnresolvedFixtureReferenceIdResolver($decoratedResolver);
        $newResolver = $resolver->withValueResolver($valueResolver);

        $this->assertEquals(new UnresolvedFixtureReferenceIdResolver($decoratedResolver), $resolver);
        $this->assertEquals(new UnresolvedFixtureReferenceIdResolver($newDecoratedResolver, $valueResolver), $newResolver);


        $resolver = new UnresolvedFixtureReferenceIdResolver($decoratedResolver, $injectedValueResolver);
        $newResolver = $resolver->withValueResolver($valueResolver);

        $this->assertEquals(
            new UnresolvedFixtureReferenceIdResolver($decoratedResolver, $injectedValueResolver),
            $resolver
        );
        $this->assertEquals(
            new UnresolvedFixtureReferenceIdResolver($newDecoratedResolver, $valueResolver),
            $newResolver
        );

        $decoratedResolverProphecy->withValueResolver(Argument::any())->shouldHaveBeenCalledTimes(2);
    }

    public function testCanResolveValuesOfItsDecoratedResolver()
    {
        $value = new FakeValue();

        $decoratedResolverProphecy = $this->prophesize(ChainableValueResolverInterface::class);
        $decoratedResolverProphecy->canResolve($value)->willReturn(true);
        /** @var ChainableValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new UnresolvedFixtureReferenceIdResolver($decoratedResolver);

        $this->assertTrue($resolver->canResolve($value));
        $decoratedResolverProphecy->canResolve(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testReturnsResultOfTheDecoratedResolverIfReferenceIdIsAString()
    {
        $value = new FixtureReferenceValue('alice');
        $expectedObject = new \stdClass();
        $expectedObject->foo = 'bar';

        $set = ResolvedFixtureSetFactory::create(
            null,
            $fixtureBag = (new FixtureBag())
                ->with(
                    $dummyFixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create())
                )
                ->with(
                    $anotherDummyFixture = new SimpleFixture('another_dummy', 'Dummy', SpecificationBagFactory::create())
                ),
            (new ObjectBag(['dummy' => $expectedObject]))
        );
        $scope = ['injected' => true];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('bar');

        $decoratedResolverProphecy = $this->prophesize(ChainableValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve($value, $dummyFixture, $set, $scope, $context)
            ->willReturn(
                $expected = new ResolvedValueWithFixtureSet(
                    $resolvedFixture = new SimpleFixture('resolved_fixture', 'Dummy', SpecificationBagFactory::create()),
                    ResolvedFixtureSetFactory::create(null, $fixtureBag->with($resolvedFixture))
                )
            )
        ;
        /** @var ChainableValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new UnresolvedFixtureReferenceIdResolver($decoratedResolver, new FakeValueResolver());
        $actual = $resolver->resolve($value, $dummyFixture, $set, $scope, $context);

        $this->assertEquals($expected, $actual);

        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testResolvesReferenceBeforeHandingOverTheResolutionToTheDecoratedResolver()
    {
        $idValue = new FakeValue();
        $value = new FixtureReferenceValue($idValue);

        $expectedObject = new \stdClass();
        $expectedObject->foo = 'bar';

        $set = ResolvedFixtureSetFactory::create(
            null,
            $fixtureBag = (new FixtureBag())
                ->with(
                    $dummyFixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create())
                )
                ->with(
                    $anotherDummyFixture = new SimpleFixture('another_dummy', 'Dummy', SpecificationBagFactory::create())
                ),
            $objectBag = (new ObjectBag(['dummy' => $expectedObject]))
        );
        $scope = ['injected' => true];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('bar');

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve($idValue, $dummyFixture, $set, $scope, $context)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                     'alice',
                     $newSet = ResolvedFixtureSetFactory::create(
                         null,
                         $fixtureBag->with(new SimpleFixture('value_resolver_fixture', 'Dummy', SpecificationBagFactory::create())),
                         $newObjectBag = $objectBag->with(new SimpleObject('value_resolver_fixture', new \stdClass()))
                     )
                 )
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $decoratedResolverProphecy = $this->prophesize(ChainableValueResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve(new FixtureReferenceValue('alice'), $dummyFixture, $newSet, $scope, $context)
            ->willReturn(
                $expected = new ResolvedValueWithFixtureSet(
                    $expectedObject,
                    ResolvedFixtureSetFactory::create(null, $fixtureBag, $newObjectBag->with(new SimpleObject('alice', $expectedObject)))
                )
            )
        ;
        /** @var ChainableValueResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $resolver = new UnresolvedFixtureReferenceIdResolver($decoratedResolver, $valueResolver);
        $actual = $resolver->resolve($value, $dummyFixture, $set, $scope, $context);

        $this->assertEquals($expected, $actual);

        $valueResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $decoratedResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException
     * @expectedExceptionMessage Expected fixture reference value "@bob" to be resolved into a string. Got "(integer) 200" instead.
     */
    public function testThrowsAnExceptionIfResolvedIdIsInvalid()
    {
        $idValue = new DummyValue('bob');
        $value = new FixtureReferenceValue($idValue);

        $dummyFixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());

        $set = ResolvedFixtureSetFactory::create();
        $scope = [];
        $context = new GenerationContext();

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve(Argument::cetera())
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    200,    // returns a new value instead of a string
                    ResolvedFixtureSetFactory::create()
                )
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $resolver = new UnresolvedFixtureReferenceIdResolver(new FakeChainableValueResolver(), $valueResolver);
        $resolver->resolve($value, $dummyFixture, $set, $scope, $context);
    }
}
