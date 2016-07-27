<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Fixture;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\DummyMethodCall;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Definition\SpecificationBag;

/**
 * @covers Nelmio\Alice\Definition\Fixture\FixtureWithFlags
 */
class FixtureWithFlagsTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAFixture()
    {
        $this->assertTrue(is_a(FixtureWithFlags::class, FixtureInterface::class, true));
    }
    
    public function testAccessors()
    {
        $reference = 'user0';
        $className = 'Nelmio\Entity\User';
        $specs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());

        $decoratedFixtureProphecy = $this->prophesize(FixtureInterface::class);
        $decoratedFixtureProphecy->getId()->willReturn($reference);
        $decoratedFixtureProphecy->getClassName()->willReturn($className);
        $decoratedFixtureProphecy->getSpecs()->willReturn($specs);
        /** @var FixtureInterface $decoratedFixture */
        $decoratedFixture = $decoratedFixtureProphecy->reveal();

        $flags = new FlagBag($reference);

        $fixture = new FixtureWithFlags($decoratedFixture, $flags);

        $this->assertEquals($reference, $fixture->getId());
        $this->assertEquals($className, $fixture->getClassName());
        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertEquals($flags, $fixture->getFlags());

        $decoratedFixtureProphecy->getId()->shouldHaveBeenCalledTimes(1);
        $decoratedFixtureProphecy->getClassName()->shouldHaveBeenCalledTimes(1);
        $decoratedFixtureProphecy->getSpecs()->shouldHaveBeenCalledTimes(1);
    }

    public function testIsImmutable()
    {
        $specs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());

        $decoratedFixtureProphecy = $this->prophesize(FixtureInterface::class);
        $decoratedFixtureProphecy->getSpecs()->willReturn($specs);
        /** @var FixtureInterface $decoratedFixture */
        $decoratedFixture = $decoratedFixtureProphecy->reveal();

        $flags = new FlagBag('something');

        $fixture = new FixtureWithFlags($decoratedFixture, $flags);

        $this->assertNotSame($fixture->getSpecs(), $fixture->getSpecs());
        $this->assertNotSame($fixture->getFlags(), $fixture->getFlags());
    }

    public function testIsDeepClonable()
    {
        /** @var FixtureInterface $decoratedFixture */
        $decoratedFixture = $this->prophesize(FixtureInterface::class)->reveal();
        $flags = new FlagBag('something');

        $fixture = new FixtureWithFlags($decoratedFixture, $flags);
        $clone = clone $fixture;

        $this->assertEquals($fixture, $clone);
        $this->assertNotSame($fixture, $clone);
    }

    public function testImmutableMutators()
    {
        $specs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());
        $newSpecs = new SpecificationBag(new DummyMethodCall('dummy'), new PropertyBag(), new MethodCallBag());

        $newDecoratedFixtureProphecy = $this->prophesize(FixtureInterface::class);
        $newDecoratedFixtureProphecy->getSpecs()->willReturn($newSpecs);
        /** @var FixtureInterface $newDecoratedFixture */
        $newDecoratedFixture = $newDecoratedFixtureProphecy->reveal();

        $decoratedFixtureProphecy = $this->prophesize(FixtureInterface::class);
        $decoratedFixtureProphecy->withSpecs($newSpecs)->willReturn($newDecoratedFixture);
        $decoratedFixtureProphecy->getSpecs()->willReturn($specs);
        /** @var FixtureInterface $decoratedFixture */
        $decoratedFixture = $decoratedFixtureProphecy->reveal();

        $flags = new FlagBag('user0');

        $fixture = new FixtureWithFlags($decoratedFixture, $flags);
        $newFixture = $fixture->withSpecs($newSpecs);

        $this->assertInstanceOf(FixtureWithFlags::class, $newFixture);
        $this->assertNotSame($fixture, $newFixture);

        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertEquals($flags, $fixture->getFlags());
        $this->assertEquals($newSpecs, $newFixture->getSpecs());
        $this->assertEquals($flags, $newFixture->getFlags());
    }
}
