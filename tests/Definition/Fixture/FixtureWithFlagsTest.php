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

use Nelmio\Alice\Definition\FakeMethodCall;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureInterface;

/**
 * @covers Nelmio\Alice\Definition\Fixture\FixtureWithFlags
 */
class FixtureWithFlagsTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAFixture()
    {
        $this->assertTrue(is_a(FixtureWithFlags::class, FixtureInterface::class, true));
    }
    
    public function testReadAccessorsReturnPropertiesValues()
    {
        $reference = 'user0';
        $className = 'Nelmio\Alice\Entity\User';
        $specs = SpecificationBagFactory::create();

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

    /**
     * @depends Nelmio\Alice\Definition\SpecificationBagTest::testIsImmutable
     * @depends Nelmio\Alice\Definition\FlagBagTest::testIsImmutable
     */
    public function testIsImmutable()
    {
        $specs = SpecificationBagFactory::create();
        $decoratedFixture = new MutableFixture('mutable', 'Mutable', $specs);
        $flags = new FlagBag('something');
        $fixture = new FixtureWithFlags($decoratedFixture, $flags);

        $newSpecs = SpecificationBagFactory::create(new FakeMethodCall());
        $decoratedFixture->setSpecs($newSpecs);

        $this->assertEquals($specs, $fixture->getSpecs());
    }

    /**
     * @depends Nelmio\Alice\Definition\SpecificationBagTest::testIsImmutable
     * @depends Nelmio\Alice\Definition\FlagBagTest::testIsImmutable
     */
    public function testWithersReturnNewModifiedInstance()
    {
        $specs = SpecificationBagFactory::create();
        $newSpecs = SpecificationBagFactory::create(new FakeMethodCall());

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
