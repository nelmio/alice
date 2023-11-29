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

namespace Nelmio\Alice\Generator\ObjectGenerator;

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\ObjectBag;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @covers \Nelmio\Alice\Generator\ObjectGenerator\SimpleObjectGenerator
 * @internal
 */
class SimpleObjectGeneratorTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAnObjectGenerator(): void
    {
        self::assertTrue(is_a(SimpleObjectGenerator::class, ObjectGeneratorInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(SimpleObjectGenerator::class))->isCloneable());
    }

    /**
     * @testdox Do a instantiate-hydrate-calls cycle to generate the object described by the fixture.
     */
    public function testGenerate(): void
    {
        self::markTestIncomplete('TODO');
        $fixture = new SimpleFixture('dummy', stdClass::class, SpecificationBagFactory::create());
        $set = ResolvedFixtureSetFactory::create();
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');
        $instance = new stdClass();
        $instantiatedObject = new SimpleObject($fixture->getId(), $instance);

        $instantiatorProphecy = $this->prophesize(InstantiatorInterface::class);
        $instantiatorProphecy
            ->instantiate($fixture, $set, $context)
            ->willReturn(
                $setWithInstantiatedObject = ResolvedFixtureSetFactory::create(
                    null,
                    null,
                    (new ObjectBag())->with($instantiatedObject),
                ),
            );
        /** @var InstantiatorInterface $instantiator */
        $instantiator = $instantiatorProphecy->reveal();

        $hydratedInstance = clone $instance;
        $hydratedInstance->hydrated = true;

        $hydratedObject = new SimpleObject($fixture->getId(), $hydratedInstance);

        $hydratorProphecy = $this->prophesize(HydratorInterface::class);
        $hydratorProphecy
            ->hydrate($instantiatedObject, $setWithInstantiatedObject, $context)
            ->willReturn(
                $setWithHydratedObject = ResolvedFixtureSetFactory::create(
                    null,
                    null,
                    (new ObjectBag())->with($hydratedObject),
                ),
            );
        /** @var HydratorInterface $hydrator */
        $hydrator = $hydratorProphecy->reveal();

        $instanceAfterCalls = clone $hydratedInstance;
        $instanceAfterCalls->calls = true;

        $objectAfterCalls = new SimpleObject($fixture->getId(), $instanceAfterCalls);

        $callerProphecy = $this->prophesize(CallerInterface::class);
        $callerProphecy
            ->doCallsOn($hydratedObject, $setWithHydratedObject)
            ->willReturn(
                $setWithObjectAfterCalls = ResolvedFixtureSetFactory::create(
                    null,
                    null,
                    (new ObjectBag())->with($objectAfterCalls),
                ),
            );
        /** @var CallerInterface $caller */
        $caller = $callerProphecy->reveal();

        $generator = new SimpleObjectGenerator(new FakeValueResolver(), $instantiator, $hydrator, $caller);
        $objects = $generator->generate($fixture, $set, $context);

        self::assertEquals($setWithObjectAfterCalls->getObjects(), $objects);

        $instantiatorProphecy->instantiate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $hydratorProphecy->hydrate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
        $callerProphecy->doCallsOn(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
