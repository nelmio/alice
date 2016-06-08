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

use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;

/**
 * @covers Nelmio\Alice\Definition\Fixture\Templating
 */
class TemplatingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideFlags
     */
    public function testDetectTemplateFlags(FlagBag $flags, bool $isATemplate, bool $extendsFixtures, array $extendedFixtures)
    {
        $templating = new Templating($flags);
        $this->assertEquals($isATemplate, $templating->isATemplate());
        $this->assertEquals($extendsFixtures, $templating->extendsFixtures());
        $this->assertEquals($extendedFixtures, $templating->getExtendedFixtures());
        $this->assertEquals(count($extendedFixtures), count($templating->getExtendedFixtures()));
    }

    public function provideFlags()
    {
        $emptyFlagBag = new FlagBag('user0');

        yield 'empty flagbag' => [
            $emptyFlagBag,
            false,
            false,
            [],
        ];

        $templateFlagBag = $emptyFlagBag->with(new TemplateFlag());

        yield 'flagbag with template' => [
            $templateFlagBag,
            true,
            false,
            [],
        ];

        $reference1 = new FixtureReference('Nelmio\Alice\User#user_base0');
        $reference2 = new FixtureReference('Nelmio\Alice\User#user_base1');
        $extendsFlagBag = $emptyFlagBag
            ->with(new ExtendFlag($reference1))
            ->with(new ExtendFlag($reference2))
        ;

        yield 'flagbag with extends' => [
            $extendsFlagBag,
            false,
            true,
            [
                $reference1,
                $reference2,
            ],
        ];

        $templateAndExtendsFlagBag = $emptyFlagBag
            ->with(new TemplateFlag())
            ->with(new ExtendFlag($reference1))
            ->with(new ExtendFlag($reference2))
        ;

        yield 'flagbag with template and extends' => [
            $templateAndExtendsFlagBag,
            true,
            true,
            [
                $reference1,
                $reference2,
            ],
        ];
    }
}
