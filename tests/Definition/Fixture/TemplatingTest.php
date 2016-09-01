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

use Nelmio\Alice\Definition\Flag\DummyFlag;
use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use Nelmio\Alice\FixtureInterface;

/**
 * @covers Nelmio\Alice\Definition\Fixture\Templating
 */
class TemplatingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @depends Nelmio\Alice\Definition\ServiceReference\FixtureReferenceTest::testIsImmutable
     */
    public function testIsImmutable()
    {
        $this->assertTrue(true, 'Nothing to do.');
    }

    /**
     * @dataProvider provideFlags
     */
    public function testDetectTemplateFlags(FixtureWithFlags $fixture, bool $isATemplate, bool $extendsFixtures, array $extendedFixtures)
    {
        $templating = new Templating($fixture);

        $this->assertEquals($isATemplate, $templating->isATemplate());
        $this->assertEquals($extendsFixtures, $templating->extendsFixtures());
        $this->assertEquals($extendedFixtures, $templating->getExtendedFixtures());
        $this->assertEquals(count($extendedFixtures), count($templating->getExtendedFixtures()));
    }

    public function provideFlags()
    {
        $emptyFlagBag = new FlagBag('user0');
        yield 'empty flagbag' => [
            $this->createFixtureWithFlags($emptyFlagBag),
            false,
            false,
            [],
        ];

        $flagBagWithNonTemplateFlag = $emptyFlagBag->withFlag(new DummyFlag());
        yield 'flagbag with non-templating element' => [
            $this->createFixtureWithFlags($flagBagWithNonTemplateFlag),
            false,
            false,
            [],
        ];

        $templateFlagBag = $emptyFlagBag->withFlag(new TemplateFlag());
        yield 'flagbag with template' => [
            $this->createFixtureWithFlags($templateFlagBag),
            true,
            false,
            [],
        ];

        $extendsFlagBag = $emptyFlagBag
            ->withFlag(new ExtendFlag(new FixtureReference('user_base0')))
            ->withFlag(new ExtendFlag(new FixtureReference('user_base1')))
        ;
        yield 'flagbag with extends' => [
            $this->createFixtureWithFlags($extendsFlagBag),
            false,
            true,
            [
                new FixtureReference('user_base0'),
                new FixtureReference('user_base1'),
            ],
        ];

        $templateAndExtendsFlagBag = $emptyFlagBag
            ->withFlag(new TemplateFlag())
            ->withFlag(new ExtendFlag(new FixtureReference('user_base0')))
            ->withFlag(new ExtendFlag(new FixtureReference('user_base1')))
            ->withFlag(new DummyFlag())
        ;
        yield 'flagbag with template, extends and non templating flags' => [
            $this->createFixtureWithFlags($templateAndExtendsFlagBag),
            true,
            true,
            [
                new FixtureReference('user_base0'),
                new FixtureReference('user_base1'),
            ],
        ];
    }

    private function createFixtureWithFlags(FlagBag $flags): FixtureWithFlags
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getClassName()->willReturn('Nelmio\Alice\Entity\User');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        return new FixtureWithFlags($fixture, $flags);
    }
}
