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
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;

/**
 * @covers Nelmio\Alice\Definition\Flag\ExtendFlag
 */
class ExtendFlagTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAFlag()
    {
        $this->assertTrue(is_a(ExtendFlag::class, FlagInterface::class, true));
    }

    public function testAccessors()
    {
        $reference = new FixtureReference('Nelmio\Alice\User#user_base');
        $flag = new ExtendFlag($reference);

        $this->assertEquals($reference, $flag->getExtendedFixture());
        $this->assertEquals('extends Nelmio\Alice\User#user_base', $flag->__toString());
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $reference = new FixtureReference('Nelmio\Alice\User#user_base');
        $flag = new ExtendFlag($reference);
        clone $flag;
    }
}
