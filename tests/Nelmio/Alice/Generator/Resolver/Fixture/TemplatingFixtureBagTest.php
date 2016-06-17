<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Fixture;

use Nelmio\Alice\Definition\Fixture\FixtureWithFlags;
use Nelmio\Alice\Definition\Fixture\TemplatingFixture;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Exception\FixtureNotFoundException;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureInterface;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Fixture\TemplatingFixtureBag
 */
class TemplatingFixtureBagTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $fixtureId = 'Nelmio\Entity\User#user0';
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn($fixtureId);
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $templateId = 'Nelmio\Entity\User#user_base';
        $templateProphecy = $this->prophesize(FixtureInterface::class);
        $templateProphecy->getId()->willReturn($templateId);
        /** @var FixtureInterface $template */
        $template = $templateProphecy->reveal();
        $template = new TemplatingFixture(
            new FixtureWithFlags(
                $template,
                (new FlagBag('user_base'))->with(new TemplateFlag())
            )
        );
        
        $bag = (new TemplatingFixtureBag())
            ->with($fixture)
            ->with($template)
        ;

        $this->assertTrue($bag->has($fixtureId));
        $this->assertEquals($fixture, $bag->get($fixtureId));

        $this->assertTrue($bag->has($templateId));
        $this->assertEquals($template, $bag->get($templateId));

        $this->assertFalse($bag->has('foo'));
        try {
            $bag->get('foo');
        } catch (FixtureNotFoundException $exception) {
            $this->assertEquals(
                'Could not find the fixture "foo".',
                $exception->getMessage()
            );
        }

        $this->assertEquals(
            (new FixtureBag())->with($fixture),
            $bag->getFixtures()
        );
    }

    public function testIsImmutable()
    {
        $fixtureId = 'Nelmio\Entity\User#user0';
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn($fixtureId);
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $bag = (new TemplatingFixtureBag())->with($fixture);

        $this->assertNotSame($bag->get($fixtureId), $bag->get($fixtureId));
        $this->assertNotSame($bag->getFixtures(), $bag->getFixtures());
    }

    public function testAddTemplateFixtureToTempalates()
    {
        $fixtureId = 'Nelmio\Entity\User#user0';
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn($fixtureId);
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $templateId = 'Nelmio\Entity\User#user_base';
        $templateProphecy = $this->prophesize(FixtureInterface::class);
        $templateProphecy->getId()->willReturn($templateId);
        /** @var FixtureInterface $template */
        $template = $templateProphecy->reveal();
        $realTemplate = new TemplatingFixture(
            new FixtureWithFlags(
                $template,
                (new FlagBag('user_base'))->with(new TemplateFlag())
            )
        );

        $templateId = 'Nelmio\Entity\User#user1';
        $templateProphecy = $this->prophesize(FixtureInterface::class);
        $templateProphecy->getId()->willReturn($templateId);
        /** @var FixtureInterface $template */
        $template = $templateProphecy->reveal();

        $falseTemplate = new FixtureWithFlags(
            $template,
            (new FlagBag('user_base'))->with(new TemplateFlag())
        );

        $bag = (new TemplatingFixtureBag())
            ->with($fixture)
            ->with($realTemplate)
            ->with($falseTemplate)
        ;

        $this->assertEquals(
            (new FixtureBag())
                ->with($fixture)
                ->with($falseTemplate)
            ,
            $bag->getFixtures()
        );
    }
}
