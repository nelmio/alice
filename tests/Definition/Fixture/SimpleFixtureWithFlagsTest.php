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

namespace Nelmio\Alice\Definition\Fixture;

use Nelmio\Alice\Definition\FakeMethodCall;
use Nelmio\Alice\Definition\FixtureWithFlagsInterface;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\Fixture\SimpleFixtureWithFlags
 */
class SimpleFixtureWithFlagsTest extends TestCase
{
    public function testIsAFixtureWithFlags()
    {
        $this->assertTrue(is_a(SimpleFixtureWithFlags::class, FixtureWithFlagsInterface::class, true));
    }
    
    public function testReadAccessorsReturnPropertiesValues()
    {
        $reference = 'user0';
        $className = 'Nelmio\Alice\Entity\User';
        $specs = SpecificationBagFactory::create();
        $valueForCurrent = 'alice';

        $decoratedFixtureProphecy = $this->prophesize(FixtureInterface::class);
        $decoratedFixtureProphecy->getId()->willReturn($reference);
        $decoratedFixtureProphecy->getClassName()->willReturn($className);
        $decoratedFixtureProphecy->getSpecs()->willReturn($specs);
        $decoratedFixtureProphecy->getValueForCurrent()->willReturn($valueForCurrent);
        /** @var FixtureInterface $decoratedFixture */
        $decoratedFixture = $decoratedFixtureProphecy->reveal();

        $flags = new FlagBag($reference);

        $fixture = new SimpleFixtureWithFlags($decoratedFixture, $flags);

        $this->assertEquals($reference, $fixture->getId());
        $this->assertEquals($className, $fixture->getClassName());
        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertEquals($valueForCurrent, $fixture->getValueForCurrent());
        $this->assertEquals($flags, $fixture->getFlags());

        $decoratedFixtureProphecy->getId()->shouldHaveBeenCalledTimes(2);
        $decoratedFixtureProphecy->getClassName()->shouldHaveBeenCalledTimes(1);
        $decoratedFixtureProphecy->getSpecs()->shouldHaveBeenCalledTimes(1);
        $decoratedFixtureProphecy->getValueForCurrent()->shouldHaveBeenCalledTimes(1);
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $reference = 'user0';
        $specs = SpecificationBagFactory::create();
        $newSpecs = SpecificationBagFactory::create(new FakeMethodCall());

        $newDecoratedFixtureProphecy = $this->prophesize(FixtureInterface::class);
        $newDecoratedFixtureProphecy->getSpecs()->willReturn($newSpecs);
        /** @var FixtureInterface $newDecoratedFixture */
        $newDecoratedFixture = $newDecoratedFixtureProphecy->reveal();

        $decoratedFixtureProphecy = $this->prophesize(FixtureInterface::class);
        $decoratedFixtureProphecy->getId()->willReturn($reference);
        $decoratedFixtureProphecy->withSpecs($newSpecs)->willReturn($newDecoratedFixture);
        $decoratedFixtureProphecy->getSpecs()->willReturn($specs);
        /** @var FixtureInterface $decoratedFixture */
        $decoratedFixture = $decoratedFixtureProphecy->reveal();

        $flags = new FlagBag('user0');

        $fixture = new SimpleFixtureWithFlags($decoratedFixture, $flags);
        $newFixture = $fixture->withSpecs($newSpecs);

        $this->assertInstanceOf(SimpleFixtureWithFlags::class, $newFixture);
        $this->assertNotSame($fixture, $newFixture);

        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertEquals($flags, $fixture->getFlags());
        $this->assertEquals($newSpecs, $newFixture->getSpecs());
        $this->assertEquals($flags, $newFixture->getFlags());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected the fixture ID and the flags key to be the same. Got "foo" and "bar" instead.
     */
    public function testThrowsAnExceptionIfFixtureIdAndFlagKeyMistmatch()
    {
        $fixture = new DummyFixture('foo');
        $flags = new FlagBag('bar');

        new SimpleFixtureWithFlags($fixture, $flags);
    }
}
