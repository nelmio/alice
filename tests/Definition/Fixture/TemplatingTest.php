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

use Nelmio\Alice\Definition\Flag\DummyFlag;
use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\Fixture\Templating
 */
class TemplatingTest extends TestCase
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
    public function testDetectTemplateFlags(SimpleFixtureWithFlags $fixture, bool $isATemplate, bool $extendsFixtures, array $extendedFixtures)
    {
        $templating = new Templating($fixture);

        $this->assertEquals($isATemplate, $templating->isATemplate());
        $this->assertEquals($extendsFixtures, $templating->extendsFixtures());
        $this->assertEquals($extendedFixtures, $templating->getExtendedFixtures());
        $this->assertCount(count($extendedFixtures), $templating->getExtendedFixtures());
    }

    /**
     * As the specs are not overridden and starting the from loaded fixture, when resolving a fixture to inherit the
     * properties of the extended fixtures, the specs should be merged with the last extended fixture to the first one.
     * For this purpose, the list of extended fixtures is given in the right order right away.
     */
    public function testExtendedFixturesOrderIsInversed()
    {
        $templating = new Templating(
            $this->createFixtureWithFlags(
                (new FlagBag(''))
                    ->withFlag(new ExtendFlag(new FixtureReference('user_base0')))
                    ->withFlag(new ExtendFlag(new FixtureReference('user_base1')))
            )
        );

        $expected = [
            new FixtureReference('user_base1'),
            new FixtureReference('user_base0'),
        ];
        $actual = $templating->getExtendedFixtures();

        $this->assertCount(count($expected), $actual);
        foreach ($expected as $index => $expectedReference) {
            $this->assertEquals($expectedReference, $actual[$index]);
        }
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
                new FixtureReference('user_base1'),
                new FixtureReference('user_base0'),
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
                new FixtureReference('user_base1'),
                new FixtureReference('user_base0'),
            ],
        ];
    }

    private function createFixtureWithFlags(FlagBag $flags): SimpleFixtureWithFlags
    {
        return new SimpleFixtureWithFlags(
            new SimpleFixture(
                $flags->getKey(),
                'Dummy',
                SpecificationBagFactory::create()
            ),
            $flags
        );
    }
}
