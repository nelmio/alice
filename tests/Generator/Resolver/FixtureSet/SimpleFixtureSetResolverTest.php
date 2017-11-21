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

namespace Nelmio\Alice\Generator\Resolver\FixtureSet;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\Generator\FixtureSetResolverInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\Resolver\FixtureBagResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\FixtureSet\SimpleFixtureSetResolver
 */
class SimpleFixtureSetResolverTest extends TestCase
{
    public function testIsAFixtureResolver()
    {
        $this->assertTrue(is_a(SimpleFixtureSetResolver::class, FixtureSetResolverInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(SimpleFixtureSetResolver::class))->isCloneable());
    }

    public function testReturnsResolvedParametersAndFixtures()
    {
        $set = new FixtureSet(
            $injectedParameters = new ParameterBag(['injected' => true]),
            $loadedParameters = new ParameterBag(['loaded' => true]),
            $fixtures = (new FixtureBag())->with(new DummyFixture('dummy')),
            $objects = (new ObjectBag())->with(new SimpleObject('injected_object', new \stdClass()))
        );

        $parameterResolverProphecy = $this->prophesize(ParameterBagResolverInterface::class);
        $parameterResolverProphecy
            ->resolve($injectedParameters, $loadedParameters)
            ->willReturn(
                $resolvedParameters = (new ParameterBag(['resolved' => true]))
            )
        ;
        /** @var ParameterBagResolverInterface $parameterResolver */
        $parameterResolver = $parameterResolverProphecy->reveal();

        $fixtureResolverProphecy = $this->prophesize(FixtureBagResolverInterface::class);
        $fixtureResolverProphecy
            ->resolve($fixtures)
            ->willReturn(
                $resolvedFixtures = (new FixtureBag())->with(new DummyFixture('another_dummy'))
            )
        ;
        /** @var FixtureBagResolverInterface $fixtureResolver */
        $fixtureResolver = $fixtureResolverProphecy->reveal();

        $expected = new ResolvedFixtureSet($resolvedParameters, $resolvedFixtures, $objects);

        $resolver = new SimpleFixtureSetResolver($parameterResolver, $fixtureResolver);
        $actual = $resolver->resolve($set);

        $this->assertEquals($expected, $actual);
    }
}
