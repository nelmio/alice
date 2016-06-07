<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Flag;

use Nelmio\Alice\Definition\FlagInterface;

/**
 * @covers Nelmio\Alice\Definition\Flag\UniqueFlag
 */
class UniqueFlagTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAFlag()
    {
        $this->assertTrue(is_a(UniqueFlag::class, FlagInterface::class, true));
    }

    public function testAccessors()
    {
        $flag = new UniqueFlag();

        $this->assertEquals('unique', $flag->__toString());
    }
}
