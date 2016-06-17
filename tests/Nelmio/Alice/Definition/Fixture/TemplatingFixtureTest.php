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

use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\DummyMethodCall;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
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
    
    public function testAccessors()
    {
        $id = 'Nelmio\Entity\User#user0';
        $reference = 'user0';
        $className = 'Nelmio\Entity\User';
        $specs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());

        $decoratedFixtureProphecy = $this->prophesize(FixtureInterface::class);
        $decoratedFixtureProphecy->getId()->willReturn($id);
        $decoratedFixtureProphecy->getReference()->willReturn($reference);
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

        $this->assertEquals($id, $fixture->getId());
        $this->assertEquals($reference, $fixture->getReference());
        $this->assertEquals($className, $fixture->getClassName());
        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertTrue($fixture->isATemplate());
        $this->assertTrue($fixture->extendsFixtures());
        $this->assertEquals([new FixtureReference('Nelmio\Entity\User#user_base')], $fixture->getExtendedFixturesReferences());

        $decoratedFixtureProphecy->getId()->shouldHaveBeenCalledTimes(1);
        $decoratedFixtureProphecy->getReference()->shouldHaveBeenCalledTimes(1);
        $decoratedFixtureProphecy->getClassName()->shouldHaveBeenCalledTimes(2);
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

        $fixtureWithFlags = new FixtureWithFlags($decoratedFixture, $flags);
        $fixture = new TemplatingFixture($fixtureWithFlags);

        $this->assertNotSame($fixture->getSpecs(), $fixture->getSpecs());
    }

    public function testIsDeepClonable()
    {
        /** @var FixtureInterface $decoratedFixture */
        $decoratedFixture = $this->prophesize(FixtureInterface::class)->reveal();
        $flags = new FlagBag('something');

        $fixtureWithFlags = new FixtureWithFlags($decoratedFixture, $flags);
        $fixture = new TemplatingFixture($fixtureWithFlags);
        $clone = clone $fixture;

        $this->assertEquals($fixture, $clone);
        $this->assertNotSame($fixture, $clone);
    }

    public function testMutatorsAreImmutable()
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

        $fixtureWithFlags = new FixtureWithFlags($decoratedFixture, $flags);
        $fixture = new TemplatingFixture($fixtureWithFlags);
        $newFixture = $fixture->withSpecs($newSpecs);

        $this->assertInstanceOf(TemplatingFixture::class, $newFixture);
        $this->assertNotSame($fixture, $newFixture);

        $this->assertEquals($specs, $fixture->getSpecs());
        $this->assertEquals($newSpecs, $newFixture->getSpecs());
    }

    public function testStripTemplateOfFlags()
    {
        $decoratedFixtureProphecy = $this->prophesize(FixtureInterface::class);
        $decoratedFixtureProphecy->getId()->shouldNotBeCalled();
        /** @var FixtureInterface $decoratedFixture */
        $decoratedFixture = $decoratedFixtureProphecy->reveal();

        $flags = new FlagBag('user0');

        $fixture = new FixtureWithFlags($decoratedFixture, $flags);
        $templatingFixture = new TemplatingFixture($fixture);

        $strippedFixtures = $templatingFixture->getStrippedFixture();

        $this->assertEquals(
            new FixtureWithFlags($fixture, new FlagBag('user0')),
            $strippedFixtures
        );

        $flags = (new FlagBag('user0'))
            ->with(new TemplateFlag())
            ->with(new ElementFlag('dummy_flag'))
        ;
        $fixture = new FixtureWithFlags($decoratedFixture, $flags);
        $templatingFixture = new TemplatingFixture($fixture);

        $strippedFixtures = $templatingFixture->getStrippedFixture();

        $this->assertEquals(
            new FixtureWithFlags(
                $fixture,
                (new FlagBag('user0'))
                    ->with(new ElementFlag('dummy_flag'))
            ),
            $strippedFixtures
        );
    }
}
