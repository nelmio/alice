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

namespace Nelmio\Alice\Generator\Resolver\Value;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Value\DummyValue;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException;
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
#[CoversClass(ValueResolverRegistry::class)]
final class ValueResolverRegistryTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAValueResolver(): void
    {
        self::assertTrue(is_a(ValueResolverRegistry::class, ValueResolverInterface::class, true));
    }

    public function testAcceptChainableInstantiators(): void
    {
        new ValueResolverRegistry([new FakeChainableValueResolver()]);
    }

    public function testThrowExceptionIfInvalidParserIsPassed(): void
    {
        $this->expectException(TypeError::class);

        new ValueResolverRegistry([new stdClass()]);
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(ValueResolverRegistry::class))->isCloneable());
    }

    public function testPicksTheFirstSuitableResolverToResolveTheGivenValue(): void
    {
        $value = new FakeValue();
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();
        $scope = ['scope' => 'epocs'];
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');
        $expected = new ResolvedValueWithFixtureSet(
            10,
            ResolvedFixtureSetFactory::create(null, null, (new ObjectBag())->with(new SimpleObject('dummy', new stdClass()))),
        );

        $instantiator1Prophecy = $this->prophesize(ChainableValueResolverInterface::class);
        $instantiator1Prophecy->canResolve($value)->willReturn(false);
        /** @var ChainableValueResolverInterface $instantiator1 */
        $instantiator1 = $instantiator1Prophecy->reveal();

        $instantiator2Prophecy = $this->prophesize(ChainableValueResolverInterface::class);
        $instantiator2Prophecy->canResolve($value)->willReturn(true);
        $instantiator2Prophecy->resolve($value, $fixture, $set, $scope, $context)->willReturn($expected);
        /** @var ChainableValueResolverInterface $instantiator2 */
        $instantiator2 = $instantiator2Prophecy->reveal();

        $instantiator3Prophecy = $this->prophesize(ChainableValueResolverInterface::class);
        $instantiator3Prophecy->canResolve(Argument::any())->shouldNotBeCalled();
        /** @var ChainableValueResolverInterface $instantiator3 */
        $instantiator3 = $instantiator3Prophecy->reveal();

        $registry = new ValueResolverRegistry([
            $instantiator1,
            $instantiator2,
            $instantiator3,
        ]);
        $actual = $registry->resolve($value, $fixture, $set, $scope, $context);

        self::assertSame($expected, $actual);

        $instantiator1Prophecy->canResolve(Argument::any())->shouldHaveBeenCalledTimes(1);
        $instantiator2Prophecy->canResolve(Argument::any())->shouldHaveBeenCalledTimes(1);
        $instantiator2Prophecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testThrowExceptionIfNoSuitableParserIsFound(): void
    {
        $fixture = new DummyFixture('dummy');

        $set = ResolvedFixtureSetFactory::create();

        $registry = new ValueResolverRegistry([]);

        $this->expectException(ResolverNotFoundException::class);
        $this->expectExceptionMessage('No resolver found to resolve value "foo".');

        $registry->resolve(new DummyValue('foo'), $fixture, $set, [], new GenerationContext());
    }
}
