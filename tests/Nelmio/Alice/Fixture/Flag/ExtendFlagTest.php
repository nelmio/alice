<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture\Flag;

use Nelmio\Alice\Fixture\FlagInterface;

/**
 * @covers Nelmio\Alice\Fixture\Flag\ExtendFlag
 */
class ExtendFlagTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAFlag()
    {
        $this->assertTrue(is_a(ExtendFlag::class, FlagInterface::class, true));
    }

    public function testAccessors()
    {
        $flag = new ExtendFlag('user0');

        $this->assertEquals('user0', $flag->getExtendedFixture());
        $this->assertEquals('extends user0', $flag->__toString());
    }
}
