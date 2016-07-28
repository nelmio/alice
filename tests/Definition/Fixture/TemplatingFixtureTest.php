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
use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\DummyMethodCall;
use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Definition\SpecificationBag;

/**
 * @covers Nelmio\Alice\Definition\Fixture\TemplatingFixture
 */
class TemplatingFixtureTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAFixture()
    {
        $this->assertTrue(is_a(TemplatingFixture::class, FixtureInterface::class, true));
    }
    
    public function testReadAccessorsReturnsPropertiesValues()
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

        $extendedFixtureReference = new FixtureReference('user_base');
        $flag1 = new TemplateFlag();
        $flag2 = new ExtendFlag($extendedFixtureReference);

        $flags = (new FlagBag($reference))
            ->with($flag1)
            ->with($flag2)
        ;

        $fixtureWithFlags = new FixtureWithFlags($decoratedFixture, $flags);
        $fixture = new TemplatingFixture($fixtureWithFlags);

        $this->assertEquals($reference, $fixture->getId());
        $this->assertEquals($className, $fixture->getClassName());
        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertTrue($fixture->isATemplate());
        $this->assertTrue($fixture->extendsFixtures());
        $this->assertEquals([new FixtureReference('user_base')], $fixture->getExtendedFixturesReferences());

        $decoratedFixtureProphecy->getId()->shouldHaveBeenCalledTimes(1);
        $decoratedFixtureProphecy->getClassName()->shouldHaveBeenCalledTimes(1);
        $decoratedFixtureProphecy->getSpecs()->shouldHaveBeenCalledTimes(1);
    }

    public function testIsImmutable()
    {
        $specs = SpecificationBagFactory::create();
        $decoratedFixture = new MutableFixture('mutable', 'Mutable', $specs);
        $flags = new FlagBag('something');

        $fixtureWithFlags = new FixtureWithFlags($decoratedFixture, $flags);
        $fixture = new TemplatingFixture($fixtureWithFlags);

        $newSpecs = SpecificationBagFactory::create(new FakeMethodCall());
        $decoratedFixture->setSpecs($newSpecs);

        $this->assertEquals($specs, $fixture->getSpecs());
    }

    public function testWithersReturnsNewModifiedInstance()
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

        $fixtureWithFlags = new FixtureWithFlags($decoratedFixture, $flags);
        $fixture = new TemplatingFixture($fixtureWithFlags);
        $newFixture = $fixture->withSpecs($newSpecs);

        $this->assertInstanceOf(TemplatingFixture::class, $newFixture);
        $this->assertNotSame($fixture, $newFixture);

        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertEquals($newSpecs, $newFixture->getSpecs());
    }

    /**
     * @dataProvider provideFlags
     */
    public function testStripFixturesOfFlagsRemovesTemplateAndExtendFlags(FlagBag $flags, FlagBag $expected)
    {
        $fixture = new FixtureWithFlags(new FakeFixture(), $flags);
        $strippedFixtures = (new TemplatingFixture($fixture))->getStrippedFixture()->getFlags();

        $this->assertEquals($expected, $strippedFixtures);
    }

    public function provideFlags()
    {
        yield 'empty flagbag' => [
            new FlagBag('user0'),
            new FlagBag('user0'),
        ];

        yield 'flagbag with one non template element' => [
            (new FlagBag('user0'))
                ->with(new ElementFlag('dummy_flag'))
            ,
            (new FlagBag('user0'))
                ->with(new ElementFlag('dummy_flag'))
            ,
        ];

        yield 'flagbag with one template flag' => [
            (new FlagBag('user0'))
                ->with(new TemplateFlag())
            ,
            new FlagBag('user0'),
        ];

        yield 'flagbag with one extend flag' => [
            (new FlagBag('user0'))
                ->with(new ExtendFlag(new FixtureReference('user_base')))
            ,
            new FlagBag('user0'),
        ];

        yield 'flagbag with multiple flags' => [
            (new FlagBag('user0'))
                ->with(new TemplateFlag())
                ->with(new ExtendFlag(new FixtureReference('user_base')))
                ->with(new ExtendFlag(new FixtureReference('random_user_template')))
                ->with(new ElementFlag('dummy_flag'))
                ->with(new ElementFlag('another_dummy_flag'))
            ,
            (new FlagBag('user0'))
                ->with(new ElementFlag('dummy_flag'))
                ->with(new ElementFlag('another_dummy_flag'))
            ,
        ];
    }
}
