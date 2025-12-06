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
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiatorNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;
use TypeError;

/**
 * @internal
 */
#[CoversClass(InstantiatorRegistry::class)]
final class InstantiatorRegistryTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAnInstantiator(): void
    {
        self::assertTrue(is_a(InstantiatorRegistry::class, InstantiatorInterface::class, true));
    }

    public function testAcceptChainableInstantiators(): void
    {
        new InstantiatorRegistry([new FakeChainableInstantiator()]);
    }

    public function testThrowExceptionIfInvalidParserIsPassed(): void
    {
        $this->expectException(TypeError::class);

        new InstantiatorRegistry([new stdClass()]);
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(InstantiatorRegistry::class))->isCloneable());
    }

    public function testPassValueResolverAwarenessPropertyToItsInstantiator(): void
    {
        $resolver = new FakeValueResolver();

        $registry = new InstantiatorRegistry([]);
        $newRegistry = $registry->withValueResolver($resolver);

        self::assertEquals(new InstantiatorRegistry([]), $registry);
        self::assertEquals(new InstantiatorRegistry([]), $newRegistry);

        $registry = new InstantiatorRegistry([new FakeChainableInstantiator()]);
        $newRegistry = $registry->withValueResolver($resolver);

        self::assertEquals(new InstantiatorRegistry([new FakeChainableInstantiator()]), $registry);
        self::assertEquals(
            new InstantiatorRegistry([new FakeChainableInstantiator()]),
            $newRegistry,
        );

        $nonAwareInstantiator = new FakeChainableInstantiator();
        // @phpstan-ignore-next-line
        $nonAwareInstantiator->foo = 'bar';

        $instantiatorProphecy = $this->prophesize(ChainableInstantiatorInterface::class);
        $instantiatorProphecy->willImplement(ValueResolverAwareInterface::class);
        $instantiatorProphecy->withValueResolver($resolver)->willReturn(new FakeChainableInstantiator());
        /** @var ChainableInstantiatorInterface $instantiator */
        $instantiator = $instantiatorProphecy->reveal();

        $registry = new InstantiatorRegistry([$nonAwareInstantiator, $instantiator]);
        $newRegistry = $registry->withValueResolver($resolver);

        self::assertEquals(new InstantiatorRegistry([$nonAwareInstantiator, $instantiator]), $registry);
        self::assertEquals(
            new InstantiatorRegistry([$nonAwareInstantiator, new FakeChainableInstantiator()]),
            $newRegistry,
        );

        $instantiatorProphecy->withValueResolver(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testIterateOverEveryInstantiatorAndUseTheFirstValidOne(): void
    {
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');
        $expected = ResolvedFixtureSetFactory::create(
            null,
            null,
            (new ObjectBag())
                ->with(new SimpleObject('dummy', new stdClass())),
        );

        $instantiator1Prophecy = $this->prophesize(ChainableInstantiatorInterface::class);
        $instantiator1Prophecy->canInstantiate($fixture)->willReturn(false);
        /** @var ChainableInstantiatorInterface $instantiator1 */
        $instantiator1 = $instantiator1Prophecy->reveal();

        $instantiator2Prophecy = $this->prophesize(ChainableInstantiatorInterface::class);
        $instantiator2Prophecy->canInstantiate($fixture)->willReturn(true);
        $instantiator2Prophecy->instantiate($fixture, $set, $context)->willReturn($expected);
        /** @var ChainableInstantiatorInterface $instantiator2 */
        $instantiator2 = $instantiator2Prophecy->reveal();

        $instantiator3Prophecy = $this->prophesize(ChainableInstantiatorInterface::class);
        $instantiator3Prophecy->canInstantiate(Argument::any())->shouldNotBeCalled();
        /** @var ChainableInstantiatorInterface $instantiator3 */
        $instantiator3 = $instantiator3Prophecy->reveal();

        $registry = new InstantiatorRegistry([
            $instantiator1,
            $instantiator2,
            $instantiator3,
        ]);
        $actual = $registry->instantiate($fixture, $set, $context);

        self::assertSame($expected, $actual);

        $instantiator1Prophecy->canInstantiate(Argument::any())->shouldHaveBeenCalledTimes(1);
        $instantiator2Prophecy->canInstantiate(Argument::any())->shouldHaveBeenCalledTimes(1);
        $instantiator2Prophecy->instantiate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testThrowExceptionIfNoSuitableParserIsFound(): void
    {
        $fixture = new DummyFixture('dummy');

        $set = ResolvedFixtureSetFactory::create();

        $registry = new InstantiatorRegistry([]);

        $this->expectException(InstantiatorNotFoundException::class);
        $this->expectExceptionMessage('No suitable instantiator found for the fixture "dummy".');

        $registry->instantiate($fixture, $set, new GenerationContext());
    }
}
