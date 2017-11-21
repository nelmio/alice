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

namespace Nelmio\Alice\Generator\Instantiator;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\ObjectBag;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Instantiator\ExistingInstanceInstantiator
 */
class ExistingInstanceInstantiatorTest extends TestCase
{
    public function testIsAnInstantiator()
    {
        $this->assertTrue(is_a(ExistingInstanceInstantiator::class, InstantiatorInterface::class, true));
    }

    public function testIsValueResolverAware()
    {
        $this->assertTrue(is_a(ExistingInstanceInstantiator::class, ValueResolverAwareInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ExistingInstanceInstantiator::class))->isCloneable());
    }

    public function testReturnsUnchangedSetIfFixtureHasAlreadyBeenInstantiated()
    {
        $fixture = new DummyFixture('dummy');
        $set = $expected = ResolvedFixtureSetFactory::create(
            null,
            null,
            (new ObjectBag())->with(
                new SimpleObject(
                    'dummy',
                    new \stdClass()
                )
            )
        );

        $instantiator = new ExistingInstanceInstantiator(new FakeInstantiator());
        $actual = $instantiator->instantiate($fixture, $set, new GenerationContext());

        $this->assertSame($expected, $actual);
    }

    public function testReturnsTheResultOfTheDecoratedInstantiatorIfTheFixtureHasNotBeenInstantiated()
    {
        $fixture = new DummyFixture('dummy');
        $set = ResolvedFixtureSetFactory::create();
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $decoratedInstantiatorProphecy = $this->prophesize(InstantiatorInterface::class);
        $decoratedInstantiatorProphecy
            ->instantiate($fixture, $set, $context)
            ->willReturn(
                $expected = $set->withObjects(
                    (new ObjectBag())->with(
                        new SimpleObject(
                            'dummy',
                            new \stdClass()
                        )
                    )
                )
            )
        ;
        /** @var InstantiatorInterface $decoratedInstantiator */
        $decoratedInstantiator = $decoratedInstantiatorProphecy->reveal();

        $instantiator = new ExistingInstanceInstantiator($decoratedInstantiator);
        $actual = $instantiator->instantiate($fixture, $set, $context);

        $this->assertSame($expected, $actual);

        $decoratedInstantiatorProphecy->instantiate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
