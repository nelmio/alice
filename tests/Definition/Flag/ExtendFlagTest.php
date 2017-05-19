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

namespace Nelmio\Alice\Definition\Flag;

use Nelmio\Alice\Definition\FlagInterface;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\Flag\ExtendFlag
 */
class ExtendFlagTest extends TestCase
{
    public function testIsAFlag()
    {
        $this->assertTrue(is_a(ExtendFlag::class, FlagInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $reference = new FixtureReference('Nelmio\Alice\EntityUser#user_base');
        $flag = new ExtendFlag($reference);

        $this->assertEquals($reference, $flag->getExtendedFixture());
        $this->assertEquals('extends Nelmio\Alice\EntityUser#user_base', $flag->__toString());
    }
}
