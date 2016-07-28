<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Value\ResolverRegistry
 */
class ResolverRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValueResolver()
    {
        $this->assertTrue(is_a(ResolverRegistry::class, ValueResolverInterface::class, true));
    }

    public function testAcceptChainableInstantiators()
    {
        new ResolverRegistry([new FakeChainableValueResolver()]);
    }

    /**
     * @expectedException \TypeError
     */
    public function testThrowExceptionIfInvalidParserIsPassed()
    {
        new ResolverRegistry([new \stdClass()]);
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $resolver = new ResolverRegistry([]);
        clone $resolver;
    }

    public function testIterateOverEveryResolverAndUseTheFirstValidOne()
    {
        $value = new FakeValue();
        $fixture = new FakeFixture();
        $set = ResolvedFixtureSetFactory::create();
        $expected = new ResolvedValueWithFixtureSet(
            10,
            ResolvedFixtureSetFactory::create(null, null, (new ObjectBag())->with(new SimpleObject('dummy', new \stdClass())))
        );

        $instantiator1Prophecy = $this->prophesize(ChainableValueResolverInterface::class);
        $instantiator1Prophecy->canResolve($value)->willReturn(false);
        /* @var ChainableValueResolverInterface $instantiator1 */
        $instantiator1 = $instantiator1Prophecy->reveal();

        $instantiator2Prophecy = $this->prophesize(ChainableValueResolverInterface::class);
        $instantiator2Prophecy->canResolve($value)->willReturn(true);
        $instantiator2Prophecy->resolve($value, $fixture, $set, [])->willReturn($expected);
        /* @var ChainableValueResolverInterface $instantiator2 */
        $instantiator2 = $instantiator2Prophecy->reveal();

        $instantiator3Prophecy = $this->prophesize(ChainableValueResolverInterface::class);
        $instantiator3Prophecy->canResolve(Argument::any())->shouldNotBeCalled();
        /* @var ChainableValueResolverInterface $instantiator3 */
        $instantiator3 = $instantiator3Prophecy->reveal();

        $registry = new ResolverRegistry([
            $instantiator1,
            $instantiator2,
            $instantiator3,
        ]);
        $actual = $registry->resolve($value, $fixture, $set);

        $this->assertSame($expected, $actual);

        $instantiator1Prophecy->canResolve(Argument::any())->shouldHaveBeenCalledTimes(1);
        $instantiator2Prophecy->canResolve(Argument::any())->shouldHaveBeenCalledTimes(1);
        $instantiator2Prophecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage No suitable value resolver found to handle the value "Nelmio\Alice\Definition\Value\FakeValue".
     */
    public function testThrowExceptionIfNoSuitableParserIsFound()
    {
        $fixture = new DummyFixture('dummy');

        $set = ResolvedFixtureSetFactory::create();

        $registry = new ResolverRegistry([]);
        $registry->resolve(new FakeValue(), $fixture, $set);
    }
}
