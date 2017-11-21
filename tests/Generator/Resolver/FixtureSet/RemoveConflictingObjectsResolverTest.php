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
use Nelmio\Alice\FixtureSetFactory;
use Nelmio\Alice\Generator\FixtureSetResolverInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\FixtureSet\RemoveConflictingObjectsResolver
 */
class RemoveConflictingObjectsResolverTest extends TestCase
{
    public function testIsAFixtureResolver()
    {
        $this->assertTrue(is_a(RemoveConflictingObjectsResolver::class, FixtureSetResolverInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(RemoveConflictingObjectsResolver::class))->isCloneable());
    }

    public function testRemovesConflictingObjectsByIteratingFixturesIfThereIsLessFixturesThanInjectedObjects()
    {
        $set = FixtureSetFactory::create();

        $decoratedResolverProphecy = $this->prophesize(FixtureSetResolverInterface::class);
        $decoratedResolverProphecy
            ->resolve($set)
            ->willReturn(
                $resolvedSet = new ResolvedFixtureSet(
                    $parameters = new ParameterBag(['resolved' => true]),
                    $fixtures = (new FixtureBag())->with(new DummyFixture('dummy')),
                    $objects = (new ObjectBag())
                        ->with(new SimpleObject('dummy', new \stdClass()))
                        ->with(new SimpleObject('another_injected_object', new \stdClass()))
                )
            )
        ;
        /** @var FixtureSetResolverInterface $decoratedResolver */
        $decoratedResolver = $decoratedResolverProphecy->reveal();

        $expected = new ResolvedFixtureSet(
            $parameters,
            $fixtures,
            $objects = (new ObjectBag())
                ->with(new SimpleObject('another_injected_object', new \stdClass()))
        );

        $resolver = new RemoveConflictingObjectsResolver($decoratedResolver);
        $actual = $resolver->resolve($set);

        $this->assertEquals($expected, $actual);
    }
}
