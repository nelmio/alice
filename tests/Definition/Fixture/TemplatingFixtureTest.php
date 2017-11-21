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
use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\Fixture\TemplatingFixture
 */
class TemplatingFixtureTest extends TestCase
{
    public function testIsAFixture()
    {
        $this->assertTrue(is_a(TemplatingFixture::class, FixtureInterface::class, true));
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

        $extendedFixtureReference = new FixtureReference('user_base');
        $flag1 = new TemplateFlag();
        $flag2 = new ExtendFlag($extendedFixtureReference);

        $flags = (new FlagBag($reference))
            ->withFlag($flag1)
            ->withFlag($flag2)
        ;

        $fixtureWithFlags = new SimpleFixtureWithFlags($decoratedFixture, $flags);
        $fixture = new TemplatingFixture($fixtureWithFlags);

        $this->assertEquals($reference, $fixture->getId());
        $this->assertEquals($className, $fixture->getClassName());
        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertEquals($valueForCurrent, $fixture->getValueForCurrent());
        $this->assertTrue($fixture->isATemplate());
        $this->assertTrue($fixture->extendsFixtures());
        $this->assertEquals([new FixtureReference('user_base')], $fixture->getExtendedFixturesReferences());
        $this->assertEquals($flags, $fixture->getFlags());

        $decoratedFixtureProphecy->getId()->shouldHaveBeenCalledTimes(2);
        $decoratedFixtureProphecy->getClassName()->shouldHaveBeenCalledTimes(1);
        $decoratedFixtureProphecy->getSpecs()->shouldHaveBeenCalledTimes(1);
        $decoratedFixtureProphecy->getValueForCurrent()->shouldHaveBeenCalledTimes(1);
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $specs = SpecificationBagFactory::create();
        $newSpecs = SpecificationBagFactory::create(new FakeMethodCall());

        $newDecoratedFixtureProphecy = $this->prophesize(FixtureInterface::class);
        $newDecoratedFixtureProphecy->getId()->willReturn('user0');
        $newDecoratedFixtureProphecy->getSpecs()->willReturn($newSpecs);
        /** @var FixtureInterface $newDecoratedFixture */
        $newDecoratedFixture = $newDecoratedFixtureProphecy->reveal();

        $decoratedFixtureProphecy = $this->prophesize(FixtureInterface::class);
        $decoratedFixtureProphecy->getId()->willReturn('user0');
        $decoratedFixtureProphecy->withSpecs($newSpecs)->willReturn($newDecoratedFixture);
        $decoratedFixtureProphecy->getSpecs()->willReturn($specs);
        /** @var FixtureInterface $decoratedFixture */
        $decoratedFixture = $decoratedFixtureProphecy->reveal();

        $flags = new FlagBag('user0');

        $fixtureWithFlags = new SimpleFixtureWithFlags($decoratedFixture, $flags);
        $fixture = new TemplatingFixture($fixtureWithFlags);
        $newFixture = $fixture->withSpecs($newSpecs);

        $this->assertInstanceOf(TemplatingFixture::class, $newFixture);
        $this->assertNotSame($fixture, $newFixture);

        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertEquals($newSpecs, $newFixture->getSpecs());
    }
}
