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

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\DummyValue;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\FakeObjectGenerator;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureReferenceResolver
 */
class FixtureReferenceResolverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->markTestIncomplete('TODO');
    }

    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(FixtureReferenceResolver::class, ChainableValueResolverInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new FixtureReferenceResolver();
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $resolver = new FixtureReferenceResolver();
        $newResolverWithGenerator = $resolver->withObjectGenerator(new FakeObjectGenerator());
        $newResolverWithResolver = $resolver->withResolver(new FakeValueResolver());
        $newResolverWithBoth = $resolver
            ->withObjectGenerator(new FakeObjectGenerator())
            ->withResolver(new FakeValueResolver())
        ;

        $this->assertEquals(new FixtureReferenceResolver(), $resolver);
        $this->assertEquals(new FixtureReferenceResolver(new FakeObjectGenerator()), $newResolverWithGenerator);
        $this->assertEquals(new FixtureReferenceResolver(null, new FakeValueResolver()), $newResolverWithResolver);
        $this->assertEquals(
            new FixtureReferenceResolver(new FakeObjectGenerator(), new FakeValueResolver()),
            $newResolverWithBoth
        );
    }

    public function testCanResolveFixtureReferenceValues()
    {
        $resolver = new FixtureReferenceResolver();

        $this->assertTrue($resolver->canResolve(new FixtureReferenceValue('')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\ObjectGenerator\ObjectGeneratorNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureReferenceResolver::resolve" to be called only if it has a generator.
     */
    public function testCannotResolveValueIfHasNoGenerator()
    {
        $resolver = new FixtureReferenceResolver();
        $resolver->resolve(new FakeValue(), new FakeFixture(), ResolvedFixtureSetFactory::create());
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\FixtureReferenceResolver::resolve" to be called only if it has a resolver.
     */
    public function testCannotResolveValueIfHasNoResolver()
    {
        $resolver = new FixtureReferenceResolver(new FakeObjectGenerator());
        $resolver->resolve(new FakeValue(), new FakeFixture(), ResolvedFixtureSetFactory::create());
    }

    public function testGenerateTheFixtureAndReturnsTheResolvedSet()
    {
        $value = new FixtureReferenceValue('dummy');
        $fixture = new DummyFixture('dummy');
        $set = ResolvedFixtureSetFactory::create(
            $parameters = new ParameterBag(['foo' => 'bar']),
            $fixtures = (new FixtureBag())->with($fixture)
        );
        $scope = ['val' => 'scopie'];

        $objectGeneratorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
        $objectGeneratorProphecy
            ->generate($fixture, $set, new GenerationContext())
            ->willReturn(
                $objects = new ObjectBag(['dummy' => new \stdClass()])
            );
        /** @var ObjectGeneratorInterface $generator */
        $generator = $objectGeneratorProphecy->reveal();

        $expected = new ResolvedValueWithFixtureSet(
            new \stdClass(),
            new ResolvedFixtureSet(
                $parameters,
                (new FixtureBag())->with($fixture),
                $objects
            )
        );

        $resolver = new FixtureReferenceResolver($generator, new FakeValueResolver());
        $actual = $resolver->resolve($value, new FakeFixture(), $set, $scope);

        $this->assertEquals($expected, $actual);

        $objectGeneratorProphecy->generate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testIfTheReferenceIsAValueThenItWillBeResolvedBeforeGenerateTheFixtureAndReturnsTheResolvedSet()
    {
        $value = new FixtureReferenceValue(new FakeValue());
        $fixture = new DummyFixture('dummy');
        $set = ResolvedFixtureSetFactory::create(
            new ParameterBag(['foo' => 'bar']),
            (new FixtureBag())->with($fixture)
        );
        $scope = ['val' => 'scopie'];

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve(new FakeValue(), $fixture, $set, $scope)
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    'dummy',
                    $newSet = ResolvedFixtureSetFactory::create(
                        $parameters = new ParameterBag(['ping' => 'pong']),
                        (new FixtureBag())->with($fixture)
                    )
                )
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $objectGeneratorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
        $objectGeneratorProphecy
            ->generate($fixture, $newSet, new GenerationContext())
            ->willReturn(
                $objects = new ObjectBag(['dummy' => new \stdClass()])
            );
        /** @var ObjectGeneratorInterface $generator */
        $generator = $objectGeneratorProphecy->reveal();

        $expected = new ResolvedValueWithFixtureSet(
            new \stdClass(),
            new ResolvedFixtureSet(
                $parameters,
                (new FixtureBag())->with($fixture),
                $objects
            )
        );

        $resolver = new FixtureReferenceResolver($generator, $valueResolver);
        $actual = $resolver->resolve($value, $fixture, $set, $scope);

        $this->assertEquals($expected, $actual);

        $valueResolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $objectGeneratorProphecy->generate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException
     * @expectedExceptionMessage Could not resolve value "@dummy".
     */
    public function testIfTheResolvedReferenceIsInvalidThenAnExceptionIsThrown()
    {
        $value = new FixtureReferenceValue(new DummyValue('dummy'));

        $valueResolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $valueResolverProphecy
            ->resolve(Argument::cetera())
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    10,
                    ResolvedFixtureSetFactory::create()
                )
            )
        ;
        /** @var ValueResolverInterface $valueResolver */
        $valueResolver = $valueResolverProphecy->reveal();

        $resolver = new FixtureReferenceResolver(new FakeObjectGenerator(), $valueResolver);
        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create());
    }
}
